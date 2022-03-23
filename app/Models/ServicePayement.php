<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ServicePayement
 * 
 * @property int $id
 * @property string $nom
 * @property string $type
 * @property string $pays_disponible
 * @property string $logo
 * @property string $etat
 * @property string $service_key
 * @property string $service_pass
 * 
 * @property \Illuminate\Database\Eloquent\Collection $associations_has_service_payements
 *
 * @package App\Models
 */
class ServicePayement extends Eloquent
{
	protected $table = 'service_payement';
	public $timestamps = false;

	protected $fillable = [
		'nom',
		'type',
		'pays_disponible',
		'logo',
		'etat',
		'service_key',
		'service_pass'
	];

	public function associations_has_service_payements()
	{
		return $this->hasMany(\App\Models\AssociationsHasServicePayement::class);
	}
}
