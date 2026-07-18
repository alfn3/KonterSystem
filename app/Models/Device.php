<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'device_id',
        'device_name',
        'is_active',
        'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function getBranchNameAttribute()
    {
        $branch = \App\Models\Branch::where('agent_id', $this->agent_id)->first();
        if ($branch) {
            return $branch->name;
        }

        $user = \App\Models\User::where('agent_id', $this->agent_id)->first();
        if (!$user) {
            return 'Tidak Diketahui';
        }

        $userName = $user->name;
        if (stripos($userName, 'Andini') !== false || stripos($this->agent_id, 'operator1') !== false) {
            return 'mobil1';
        } elseif (stripos($userName, 'Budi') !== false) {
            return 'mobil2';
        } elseif (stripos($userName, 'Siti') !== false) {
            return 'toko';
        } elseif (stripos($userName, 'Dewi') !== false) {
            return 'mobil4';
        }

        return $userName;
    }
}
