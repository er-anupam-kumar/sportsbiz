<?php

namespace App\Support;

class TenantContext
{
    public function __construct(private ?int $adminId = null)
    {
    }

    public function setAdminId(?int $adminId): void
    {
        $this->adminId = $adminId;
    }

    public function adminId(): ?int
    {
        return $this->adminId;
    }
}
