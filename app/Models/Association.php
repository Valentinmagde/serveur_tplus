<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Association
 * 
 * @property int $id
 * @property string $nom
 * @property string $description
 * @property int $date_creation
 * @property string $pays
 * @property string $ville
 * @property string $fuseau_horaire
 * @property string $devise
 * @property bool $etat
 * @property string $visibilite_financiere
 * @property bool $public
 * @property bool $moderation_contenu
 * @property string $presentation
 * @property int $create_at
 * @property int $update_at
 * @property string $email
 * @property string $logo
 * @property string $slogan
 * @property int $admin_id
 * @property int $max_size
 * @property string $telephone
 * @property string $langue
 * @property int $a_wallets_id
 * 
 * @property \App\Models\AWallet $a_wallet
 * @property \Illuminate\Database\Eloquent\Collection $activites
 * @property \Illuminate\Database\Eloquent\Collection $cycles
 * @property \Illuminate\Database\Eloquent\Collection $documents
 * @property \Illuminate\Database\Eloquent\Collection $invitations
 * @property \Illuminate\Database\Eloquent\Collection $membres
 * @property \Illuminate\Database\Eloquent\Collection $nouvelles
 * @property \Illuminate\Database\Eloquent\Collection $privileges
 * @property \Illuminate\Database\Eloquent\Collection $type_assistances
 * @property \Illuminate\Database\Eloquent\Collection $type_sanctions
 *
 * @package App\Models
 */
class Association extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'date_creation' => 'int',
		'etat' => 'bool',
		'public' => 'bool',
		'moderation_contenu' => 'bool',
		'create_at' => 'int',
		'update_at' => 'int',
		'admin_id' => 'int',
		'max_size' => 'int',
		'a_wallets_id' => 'int'
	];

	protected $fillable = [
		'nom',
		'description',
		'date_creation',
		'pays',
		'ville',
		'fuseau_horaire',
		'devise',
		'etat',
		'visibilite_financiere',
		'public',
		'moderation_contenu',
		'presentation',
		'create_at',
		'update_at',
		'email',
		'logo',
		'slogan',
		'admin_id',
		'max_size',
		'telephone',
		'langue',
		'a_wallets_id'
	];

	public function a_wallet()
	{
		return $this->belongsTo(\App\Models\AWallet::class, 'a_wallets_id');
	}

	public function activites()
	{
		return $this->hasMany(\App\Models\Activite::class, 'associations_id');
	}

	public function cycles()
	{
		return $this->hasMany(\App\Models\Cycle::class, 'associations_id');
	}

	public function documents()
	{
		return $this->hasMany(\App\Models\Document::class, 'associations_id');
	}

	public function invitations()
	{
		return $this->hasMany(\App\Models\Invitation::class, 'associations_id');
	}

	public function membres()
	{
		return $this->hasMany(\App\Models\Membre::class, 'associations_id');
	}

	public function nouvelles()
	{
		return $this->hasMany(\App\Models\Nouvelle::class, 'associations_id');
	}

	public function privileges()
	{
		return $this->hasMany(\App\Models\Privilege::class, 'associations_id');
	}

	public function type_assistances()
	{
		return $this->hasMany(\App\Models\TypeAssistance::class, 'associations_id');
	}

	public function type_sanctions()
	{
		return $this->hasMany(\App\Models\TypeSanction::class, 'associations_id');
	}
}
