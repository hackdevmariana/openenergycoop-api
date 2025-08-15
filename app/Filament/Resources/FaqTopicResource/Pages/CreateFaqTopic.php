<?php

namespace App\Filament\Resources\FaqTopicResource\Pages;

use App\Filament\Resources\FaqTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqTopic extends CreateRecord
{
    protected static string $resource = FaqTopicResource::class;
}
