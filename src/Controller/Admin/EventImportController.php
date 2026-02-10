<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Service\EventImportProcessor;
use App\Service\SpreadsheetReader;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/event-import')]
class EventImportController extends AbstractController
{
    public function __construct(
        private SpreadsheetReader $reader,
        private EventImportProcessor $processor,
        private AdminUrlGenerator $urlGenerator,
    ) {
    }

    #[Route('', name: 'admin_event_import_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request): Response
    {
        $eventsUrl = $this->urlGenerator
            ->setController(EventCrudController::class)
            ->setAction('index')
            ->generateUrl();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('event_import_upload', $request->request->get('_token'))) {
                $this->addFlash('danger', 'Jeton CSRF invalide.');

                return $this->redirectToRoute('admin_event_import_upload');
            }

            /** @var UploadedFile|null $file */
            $file = $request->files->get('import_file');
            if ($file === null) {
                $this->addFlash('danger', 'Veuillez sélectionner un fichier.');

                return $this->redirectToRoute('admin_event_import_upload');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'xlsx'], true)) {
                $this->addFlash('danger', 'Format de fichier non supporté. Utilisez CSV ou XLSX.');

                return $this->redirectToRoute('admin_event_import_upload');
            }

            $tempDir = sys_get_temp_dir();
            $tempName = uniqid('event_import_') . '.' . $extension;
            $file->move($tempDir, $tempName);

            $request->getSession()->set('event_import_file', $tempDir . '/' . $tempName);

            return $this->redirectToRoute('admin_event_import_map');
        }

        return $this->render('admin/event_import/upload.html.twig', [
            'events_url' => $eventsUrl,
        ]);
    }

    #[Route('/map', name: 'admin_event_import_map', methods: ['GET', 'POST'])]
    public function map(Request $request): Response
    {
        $filePath = $request->getSession()->get('event_import_file');
        if ($filePath === null || !file_exists($filePath)) {
            $this->addFlash('danger', 'Aucun fichier à traiter. Veuillez en uploader un.');

            return $this->redirectToRoute('admin_event_import_upload');
        }

        $headers = $this->reader->readHeaders($filePath);
        $previewRows = $this->reader->readPreviewRows($filePath);
        $fieldMap = EventImportProcessor::FIELD_MAP;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('event_import_map', $request->request->get('_token'))) {
                $this->addFlash('danger', 'Jeton CSRF invalide.');

                return $this->redirectToRoute('admin_event_import_map');
            }

            /** @var array<string, string> $mapping */
            $mapping = $request->request->all('mapping');

            $activeMappings = array_filter($mapping, fn (string $v) => $v !== '');
            $mappedFields = array_values($activeMappings);

            foreach ($fieldMap as $field => $config) {
                if ($config['required'] && !in_array($field, $mappedFields, true)) {
                    $this->addFlash('danger', sprintf('Le champ "%s" est requis et doit être mappé.', $config['label']));

                    return $this->redirectToRoute('admin_event_import_map');
                }
            }

            $rows = $this->reader->readAllRows($filePath);
            $result = $this->processor->process($rows, $activeMappings);

            @unlink($filePath);
            $request->getSession()->remove('event_import_file');
            $request->getSession()->set('event_import_result', [
                'successCount' => $result->successCount,
                'errors' => $result->getErrors(),
            ]);

            return $this->redirectToRoute('admin_event_import_results');
        }

        return $this->render('admin/event_import/map.html.twig', [
            'headers' => $headers,
            'preview_rows' => $previewRows,
            'field_map' => $fieldMap,
        ]);
    }

    #[Route('/results', name: 'admin_event_import_results', methods: ['GET'])]
    public function results(Request $request): Response
    {
        $resultData = $request->getSession()->get('event_import_result');
        if ($resultData === null) {
            return $this->redirectToRoute('admin_event_import_upload');
        }

        $request->getSession()->remove('event_import_result');

        $eventsUrl = $this->urlGenerator
            ->setController(EventCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->render('admin/event_import/results.html.twig', [
            'success_count' => $resultData['successCount'],
            'errors' => $resultData['errors'],
            'events_url' => $eventsUrl,
        ]);
    }
}
