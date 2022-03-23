<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FcmToken
 * 
 * @property int $id
 * @property string $token
 * @property int $utilisateurs_id
 * 
 * @property \App\Models\Utilisateur $utilisateur
 *
 * @package App\Models
 */
class FcmToken extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'utilisateurs_id' => 'int'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'token',
		'utilisateurs_id'
	];

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}
}
