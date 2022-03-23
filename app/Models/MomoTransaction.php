<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Oct 2020 15:21:40 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class MomoTransaction
 * 
 * @property int $id
 * @property string $tel
 * @property int $amount
 * @property bool $status
 * @property string $desc
 * @property string $comment
 * @property string $transaction_id
 * @property string $reference
 * @property string $receiver_tel
 * @property string $operation_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 */
class MomoTransaction extends Eloquent
{
	protected $casts = [
		'amount' => 'int',
		'status' => 'bool'
	];

	protected $fillable = [
		'tel',
		'amount',
		'status',
		'desc',
		'comment',
		'transaction_id',
		'reference',
		'receiver_tel',
		'operation_type'
	];
}
