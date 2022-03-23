<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class PersonnesAffiliee
 * 
 * @property int $id
 * @property int $membres_id
 * @property int $PAMs_id
 * @property int $PAMs_activites_id
 * @property int $date_affiliation
 * @property string $etat
 * @property string $nom
 * @property string $prenom
 * @property int $date_naissance
 * @property string $numero_ID
 * @property string $adresse
 * 
 * @property \App\Models\Activite $activite
 * @property \App\Models\Pam $pam
 * @property \App\Models\Membre $membre
 *
 * @package App\Models
 */
class PersonnesAffiliee extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'membres_id' => 'int',
		'PAMs_id' => 'int',
		'PAMs_activites_id' => 'int',
		'date_affiliation' => 'int',
		'date_naissance' => 'int'
	];

	protected $fillable = [
		'membres_id',
		'PAMs_id',
		'PAMs_activites_id',
		'date_affiliation',
		'etat',
		'nom',
		'prenom',
		'date_naissance',
		'numero_ID',
		'adresse'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'PAMs_activites_id');
	}

	public function pam()
	{
		return $this->belongsTo(\App\Models\Pam::class, 'PAMs_id');
	}

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}
}
