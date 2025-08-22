<?php

namespace App\Filament\Resources\ProductionProjectResource\Pages;

use App\Filament\Resources\ProductionProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionProject extends CreateRecord
{
    protected static string $resource = ProductionProjectResource::class;
}
