<?php

declare(strict_types = 1);

namespace App\Form;

class FileListForm extends BaseListForm
{
    protected static array $orderByColumnMap = [
        'createdAt' => 'created_at',
        'name' => 'filename',
    ];
}
