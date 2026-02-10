<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\EventType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Event')
            ->setEntityLabelInPlural('Events')
            ->setSearchFields(['name', 'description'])
            ->setDefaultSort(['eventDate' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $importAction = Action::new('importEvents', 'Import', 'fa fa-file-import')
            ->linkToRoute('admin_event_import_upload')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $importAction);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield AssociationField::new('season');
        yield TextareaField::new('description')->hideOnIndex();
        yield ChoiceField::new('type')
            ->setChoices(array_combine(
                array_map(fn (EventType $t) => $t->label(), EventType::cases()),
                EventType::cases(),
            ));
        yield DateField::new('eventDate', 'Event Date');
        yield AssociationField::new('categories')
            ->setFormTypeOption('by_reference', false);
    }
}
