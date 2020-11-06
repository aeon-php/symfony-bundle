<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use Aeon\Symfony\AeonBundle\Validator\Constraints\NotHolidayValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();

    $services->set('calendar.holidays.validator.not_holiday', NotHolidayValidator::class)
        ->args([ref('aeon.calendar.holidays.factory')])
        ->tag('validator.constraint_validator', ['alias' => 'calendar.holidays.validator.not_holiday'])
        ->alias(NotHolidayValidator::class, 'calendar.holidays.validator.not_holiday');
};
