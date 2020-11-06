<?php

declare(strict_types=1);

namespace Aeon\Symfony\AeonBundle\Tests\Functional\App\Form;

use Aeon\Symfony\AeonBundle\Form\Type\AeonDayType;
use Aeon\Symfony\AeonBundle\Validator\Constraints\NotHoliday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NotHolidaysFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->setMethod('POST');

        $builder->add('day', AeonDayType::class, [
            'widget' => 'single_text',
            'input' => 'string',
            'constraints' => [new NotHoliday(['countryCode' => 'US'])],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'not_holidays';
    }
}
