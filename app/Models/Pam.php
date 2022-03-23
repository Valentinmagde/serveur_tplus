<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Pam
 * 
 * @property int $id
 * @property int $activites_id
 * @property string $presentation
 * @property int $create_at
 * @property string $pays
 * @property string $ville
 * @property string $email
 * @property string $telephone
 * @property float $montant_prime
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $personnes_affiliees
 *
 * @package App\Models
 */
class Pam extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'create_at' => 'int',
		'montant_prime' => 'float'
	];

	protected $fillable = [
		'activites_id',
		'presentation',
		'create_at',
		'pays',
		'ville',
		'email',
		'telephone',
		'montant_prime'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function personnes_affiliees()
	{
		return $this->hasMany(\App\Models\PersonnesAffiliee::class, 'PAMs_id');
	}
}
