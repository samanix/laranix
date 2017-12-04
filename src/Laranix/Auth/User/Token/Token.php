<?php
namespace Laranix\Auth\User\Token;

use Laranix\Auth\User\User;
use Laranix\Support\Database\Model;

abstract class Token extends Model
{
    const TOKEN_VALID = 1;
    const TOKEN_INVALID = 2;
    const TOKEN_EXPIRED = 3;

    /**
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['token'];

    /**
     * Whether token is valid or not
     *
     * @var int
     */
    public $tokenStatus = self::TOKEN_INVALID;

    /**
     * Get user assigned to email verification model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get token status
     *
     * @return int
     */
    public function getStatusAttribute() : int
    {
        return $this->tokenStatus;
    }
}
