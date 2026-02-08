<?php

namespace App\Controller\Admin;

use App\Entity\PlayerCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlayerCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayerCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Player Category')
            ->setEntityLabelInPlural('Player Categories')
            ->setSearchFields(['code', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('code');
        yield TextField::new('description');
    }
}
