<?php
/**
 * @date      09/01/18 12:03
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\Form\Type\ProcessManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ScheduledExportType
 *
 * @package Divante\ScheduledExportBundle\Form\Type\ProcessManager
 */
class ScheduledExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('grid_config', TextType::class)
            ->add('objects_folder', TextType::class)
            ->add('asset_folder', TextType::class)
            ->add('asset_filename', TextType::class)
            ->add('condition', TextType::class)
            ->add('delimiter', TextType::class)
            ->add('add_timestamp', CheckboxType::class)
            ->add('timestamp', TextType::class)
            ->add('only_changes', CheckboxType::class)
            ->add('divide_file', TextType::class)
            ->add('preserve_process', CheckboxType::class)
            ->add('types', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'process_manager_process_scheduled_export';
    }
}
