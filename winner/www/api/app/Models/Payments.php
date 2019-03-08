<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{

    protected $table = 'payments';
    protected $primaryKey = 'id';

    protected $fillable = array('merchant_id', 'orders_id', 'methods', 'methods_type', 'amount', 'currency', 'approval_code', 'txn', 'txn_datetime', 'paid_channel', 'payment_status', 'payment_code', 'payment_message', 'raw_data', 'approve_remark', 'validate_status', 'validate_remark', 'validate_file_csv', 'is_canceled');
    protected $guarded = array('id', 'approve_datetime', 'approve_by', 'validate_datetime', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function orders() {
        return $this->hasOne('\App\Models\Orders', 'id', 'orders_id');
    }

}




