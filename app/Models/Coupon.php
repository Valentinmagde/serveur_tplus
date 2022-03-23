<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Oct 2020 15:21:40 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Coupon
 * 
 * @property int $id
 * @property string $code
 * @property int $pourcentage
 *
 * @package App\Models
 */
class Coupon extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'montant' => 'int',
		'pourcentage' => 'int',
		'date_limite' => 'int',
		'created_by' => 'int',
		'created_at' => 'int',
	];

	protected $fillable = [
		'code',
		'type',
		'montant',
		'pourcentage',
		'date_limite',
		'created_by',
		'created_at'
	];
}
