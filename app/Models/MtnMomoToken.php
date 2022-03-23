<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Oct 2020 15:21:40 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class MtnMomoToken
 * 
 * @property int $id
 * @property string $access_token
 * @property string $refresh_token
 * @property string $token_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $expires_at
 * @property string $deleted_at
 *
 * @package App\Models
 */
class MtnMomoToken extends Eloquent
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $dates = [
		'expires_at'
	];

	protected $hidden = [
		'access_token',
		'refresh_token'
	];

	protected $fillable = [
		'access_token',
		'refresh_token',
		'token_type',
		'expires_at'
	];
}
