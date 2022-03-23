<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class UtilisateursHasCategoriesNotification
 * 
 * @property int $utilisateurs_id
 * @property int $categories_notifications_id
 *
 * @package App\Models
 */
class UtilisateursHasCategoriesNotification extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'utilisateurs_id' => 'int',
		'categories_notifications_id' => 'int'
	];

	protected $fillable = [
		'utilisateurs_id',
		'categories_notifications_id'
	];
}
