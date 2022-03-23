<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 24 Jan 2020 13:51:52 +0000.
 */

namespace App\Models;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Utilisateur
 * 
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $source
 * @property string $sexe
 * @property int $date_nais
 * @property string $photo_couverture
 * @property string $photo_profil
 * @property string $remember_token
 * @property int $active
 * @property string $pays
 * @property string $ville
 * @property string $anniversaire
 * @property int $created_at
 * @property int $updated_at
 * @property string $activation_token
 * @property string $code
 * @property string $deleted_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $membres_has_users
 * @property \Illuminate\Database\Eloquent\Collection $messages
 * @property \Illuminate\Database\Eloquent\Collection $recevoirs
 * @property \Illuminate\Database\Eloquent\Collection $taches
 *
 * @package App\Models
 */
class Utilisateur extends Authenticatable implements \Illuminate\Contracts\Auth\Authenticatable
{
	use \Illuminate\Database\Eloquent\SoftDeletes;
	use HasApiTokens, Notifiable;

	public $timestamps = false;

	protected $casts = [
		'date_nais' => 'int',
		'active' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $hidden = [
		'password',
		'remember_token',
		'activation_token'
	];

	protected $fillable = [
		'firstName',
		'lastName',
		'email',
		'phone',
		'password',
		'source',
		'sexe',
		'date_nais',
		'photo_couverture',
		'photo_profil',
		'remember_token',
		'active',
		'pays',
		'ville',
		'anniversaire',
		'activation_token',
		'code',
		'presentation',
		'created_at',
		'updated_at',
		'presentation'
	];

	public function membres_has_users()
	{
		return $this->hasMany(\App\Models\MembresHasUser::class, 'utilisateurs_id');
	}

	public function messages()
	{
		return $this->hasMany(\App\Models\Message::class, 'utilisateurs_id');
	}

	public function recevoirs()
	{
		return $this->hasMany(\App\Models\Recevoir::class, 'utilisateurs_id');
	}

	public function taches()
	{
		return $this->hasMany(\App\Models\Tach::class, 'utilisateurs_id');
	}
}
