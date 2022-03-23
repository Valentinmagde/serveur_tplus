<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Recevoir
 * 
 * @property int $id
 * @property int $utilisateurs_id
 * @property int $messages_id
 * 
 * @property \App\Models\Utilisateur $utilisateur
 * @property \App\Models\Message $message
 *
 * @package App\Models
 */
class Recevoir extends Eloquent
{
	protected $table = 'recevoir';
	public $timestamps = false;

	protected $casts = [
		'utilisateurs_id' => 'int',
		'messages_id' => 'int'
	];

	protected $fillable = [
		'utilisateurs_id',
		'messages_id'
	];

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}

	public function message()
	{
		return $this->belongsTo(\App\Models\Message::class, 'messages_id');
	}
}
