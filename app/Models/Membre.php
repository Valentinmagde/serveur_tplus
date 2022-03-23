<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Membre
 * 
 * @property int $id
 * @property int $associations_id
 * @property string $firstName
 * @property int $date_created
 * @property string $created_by
 * @property string $code
 * @property string $lastName
 * @property string $etat
 * @property int $create_at
 * @property int $update_at
 * @property string $adresse
 * @property string $phone
 * @property int $default_u_wallets_id
 * 
 * @property \App\Models\Association $association
 * @property \App\Models\UWallet $u_wallet
 * @property \Illuminate\Database\Eloquent\Collection $ags
 * @property \Illuminate\Database\Eloquent\Collection $assistances
 * @property \Illuminate\Database\Eloquent\Collection $avalistes
 * @property \Illuminate\Database\Eloquent\Collection $commentaire_nouvelles
 * @property \Illuminate\Database\Eloquent\Collection $comptes
 * @property \Illuminate\Database\Eloquent\Collection $credits
 * @property \Illuminate\Database\Eloquent\Collection $echeanciers
 * @property \Illuminate\Database\Eloquent\Collection $invitations
 * @property \Illuminate\Database\Eloquent\Collection $membres_has_users
 * @property \Illuminate\Database\Eloquent\Collection $mise_de_fonds
 * @property \Illuminate\Database\Eloquent\Collection $nouvelles
 * @property \Illuminate\Database\Eloquent\Collection $operations
 * @property \Illuminate\Database\Eloquent\Collection $personnes_affiliees
 * @property \Illuminate\Database\Eloquent\Collection $presence_evenements
 * @property \Illuminate\Database\Eloquent\Collection $presences
 * @property \Illuminate\Database\Eloquent\Collection $sanctions
 *
 * @package App\Models
 */
class Membre extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'date_created' => 'int',
		'create_at' => 'int',
		'update_at' => 'int',
		'default_u_wallets_id' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'firstName',
		'date_created',
		'created_by',
		'code',
		'lastName',
		'etat',
		'create_at',
		'update_at',
		'adresse',
		'phone',
		'default_u_wallets_id'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function u_wallet()
	{
		return $this->belongsTo(\App\Models\UWallet::class, 'default_u_wallets_id');
	}

	public function ags()
	{
		return $this->hasMany(\App\Models\Ag::class, 'membres_id');
	}

	public function assistances()
	{
		return $this->hasMany(\App\Models\Assistance::class, 'membres_id');
	}

	public function avalistes()
	{
		return $this->hasMany(\App\Models\Avaliste::class, 'membres_id2');
	}

	public function commentaire_nouvelles()
	{
		return $this->hasMany(\App\Models\CommentaireNouvelle::class, 'membres_id');
	}

	public function comptes()
	{
		return $this->hasMany(\App\Models\Compte::class, 'membres_id');
	}

	public function credits()
	{
		return $this->hasMany(\App\Models\Credit::class, 'membres_id');
	}

	public function echeanciers()
	{
		return $this->hasMany(\App\Models\Echeancier::class, 'membres_id');
	}

	public function invitations()
	{
		return $this->hasMany(\App\Models\Invitation::class, 'membres_id');
	}

	public function membres_has_users()
	{
		return $this->hasMany(\App\Models\MembresHasUser::class, 'membres_id');
	}

	public function mise_de_fonds()
	{
		return $this->hasMany(\App\Models\MiseDeFond::class, 'membres_id');
	}

	public function nouvelles()
	{
		return $this->hasMany(\App\Models\Nouvelle::class, 'membres_id');
	}

	public function operations()
	{
		return $this->hasMany(\App\Models\Operation::class);
	}

	public function personnes_affiliees()
	{
		return $this->hasMany(\App\Models\PersonnesAffiliee::class, 'membres_id');
	}

	public function presence_evenements()
	{
		return $this->hasMany(\App\Models\PresenceEvenement::class, 'membres_id');
	}

	public function presences()
	{
		return $this->hasMany(\App\Models\Presence::class, 'membres_id');
	}

	public function sanctions()
	{
		return $this->hasMany(\App\Models\Sanction::class, 'membres_id');
	}
}
