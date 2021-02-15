<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserMultipleFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder
        ->add('type', ChoiceType::class, [
            'choices'  => [
                '-- select --' => '',
                'Delete' => 'remove_users'
            ],
            'label' => 'Selected'
        ])
        ->add('ids', ChoiceType::class, [
            'multiple' => true,
            'expanded' => true,
            'label' => false,
            'choices'=> []
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Execute'
        ])
      ;

      $builder->addEventListener(
        FormEvents::PRE_SUBMIT,
        function(FormEvent $event){
            $data = $event->getData();
            
            if(isset($data['ids'])){
                
                $ids = $data['ids'];
            
                $choices = [];
                
                if(is_array($ids)){
                    foreach($ids as $choice){
                        $choices[$choice] = $choice;
                    }
                }
                else{
                    $choices[$ids] = $ids;
                }
                
                $form = $event->getForm();
                $form->add('ids', ChoiceType::class, [
                    'multiple' => true,
                    'expanded' => true,
                    'choices'=> $choices
                ]);
            }
        }
      );
  }
}