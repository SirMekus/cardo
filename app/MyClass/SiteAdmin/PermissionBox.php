<?php

namespace App\MyClass\SiteAdmin;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionBox
{
    public $rolesWithPermission;

    public function __construct(public $model, public String $permission)
    {
        $permission = $this->permission;

        $this->rolesWithPermission = Permission::whereHas('origin', function (Builder $query) use ($permission) {
            $query->where('permission', $permission);
        })->where('school_id', $this->model->branch->school->id)->get();
    }

    public function permissionNotCreated(): bool
    {
        return (count($this->rolesWithPermission->toArray()) <= 0) ? true : false;
    }

    public function hasPermission(): bool
    {
        return (in_array($this->model->role?->id, $this->rolesWithPermission->pluck('role_id')->toArray())) ? true : false;
    }
}
