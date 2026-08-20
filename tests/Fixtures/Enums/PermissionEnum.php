<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Enums;

use AhmedNour\AttributeRouting\Contracts\Permitted;

enum PermissionEnum: string implements Permitted
{
    case VIEW_LEADS = 'view_leads';
    case CREATE_LEAD = 'create_lead';
    case EDIT_LEAD = 'edit_lead';
    case DELETE_LEAD = 'delete_lead';

    public function getMiddleware(): string
    {
        return 'permission:'.$this->value;
    }
}
