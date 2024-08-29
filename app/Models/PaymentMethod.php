<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MembershipPayment;
use App\Models\FinePayment;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function finePayments(){
        return $this->hasMany(FinePayment::class);
    }

    public function membershipPayments(){
        return $this->hasMany(MembershipPayment::class);
    }
}
