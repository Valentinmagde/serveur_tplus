<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AssociationsHasServicePayement
 * 
 * @property int $id
 * @property int $associations_id
 * @property int $service_payement_id
 * @property string $service_compte
 * @property int $create_at
 * @property int $update_at
 * @property string $service_prop1
 * @property string $service_prop2
 * 
 * @property \App\Models\Assistance $assistance
 * @property \App\Models\ServicePayement $service_payement
 *
 * @package App\Models
 */
class AssociationsHasServicePayement extends Eloquent
{
	protected $table = 'associations_has_service_payement';
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'service_payement_id' => 'int',
		'create_at' => 'int',
		'update_at' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'service_payement_id',
		'service_compte',
		'create_at',
		'update_at',
		'service_prop1',
		'service_prop2'
	];

	public function assistance()
	{
		return $this->belongsTo(\App\Models\Assistance::class, 'associations_id');
	}

	public function service_payement()
	{
		return $this->belongsTo(\App\Models\ServicePayement::class);
	}
}
