<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CommentaireNouvelle
 * 
 * @property int $id
 * @property int $nouvelles_id
 * @property int $membres_id
 * @property bool $aime
 * @property bool $aime_pas
 * @property string $commentaire
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \App\Models\Membre $membre
 * @property \App\Models\Nouvelle $nouvelle
 *
 * @package App\Models
 */
class CommentaireNouvelle extends Eloquent
{

	public $timestamps = false;
	protected $casts = [
		'nouvelles_id' => 'int',
		'membres_id' => 'int',
		'aime' => 'bool',
		'aime_pas' => 'bool',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $fillable = [
		'nouvelles_id',
		'membres_id',
		'aime',
		'aime_pas',
		'commentaire'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function nouvelle()
	{
		return $this->belongsTo(\App\Models\Nouvelle::class, 'nouvelles_id');
	}
}
