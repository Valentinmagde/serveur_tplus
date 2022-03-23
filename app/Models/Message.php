<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Message
 * 
 * @property int $id
 * @property int $utilisateurs_id
 * @property string $contenu
 * @property int $create_at
 * @property int $create_by
 * @property bool $statut
 * 
 * @property \App\Models\Utilisateur $utilisateur
 * @property \Illuminate\Database\Eloquent\Collection $recevoirs
 *
 * @package App\Models
 */
class Message extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'utilisateurs_id' => 'int',
		'create_at' => 'int',
		'create_by' => 'int',
		'statut' => 'bool'
	];

	protected $fillable = [
		'utilisateurs_id',
		'contenu',
		'create_at',
		'create_by',
		'statut'
	];

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}

	public function recevoirs()
	{
		return $this->hasMany(\App\Models\Recevoir::class, 'messages_id');
	}
}
