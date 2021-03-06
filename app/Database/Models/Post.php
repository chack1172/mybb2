<?php
/**
 * Post model class.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/core
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Core\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use MyBB\Core\Content\ContentInterface;
use MyBB\Core\Likes\Traits\LikeableTrait;
use MyBB\Core\Moderation\Moderations\ApprovableInterface;
use MyBB\Core\Presenters\PostPresenter;

/**
 * @property int topic_id
 * @property Topic topic
 * @property int id
 * @property User author
 */
class Post extends Model implements HasPresenter, ApprovableInterface, ContentInterface
{
    use SoftDeletes;
    use LikeableTrait;

    // @codingStandardsIgnoreStart

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // @codingStandardsIgnoreEnd

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The date attributes.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $casts = [
        'topic_id' => 'int',
    ];

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass() : string
    {
        return PostPresenter::class;
    }

    /**
     * A post belongs to a topic.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo(\MyBB\Core\Database\Models\Topic::class)->withTrashed();
    }

    /**
     * A post is created by (and belongs to) a user/author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(\MyBB\Core\Database\Models\User::class, 'user_id');
    }

    /**
     * @return bool|int
     */
    public function approve()
    {
        $result = $this->update(['approved' => 1]);

        if ($result && !$this->topic->approved) {
            $this->topic->approve();
        }

        return $result;
    }

    /**
     * @return bool|int
     */
    public function unapprove()
    {
        $result = $this->update(['approved' => 0]);

        if ($result && $this->topic->approved) {
            $this->topic->unapprove();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return 'post';
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return route('posts.show', ['id' => $this->id]);
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return null;
    }
}
