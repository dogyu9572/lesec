<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class Board extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'skin_id',
        'template_id',
        'is_active',
        'list_count',
        'enable_notice',
        'is_single_page',
        'enable_sorting',
        'permission_read',
        'permission_write',
        'permission_comment',
        'field_config',
        'custom_fields_config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'list_count' => 'integer',
        'enable_notice' => 'boolean',
        'is_single_page' => 'boolean',
        'enable_sorting' => 'boolean',
        'field_config' => 'array',
        'custom_fields_config' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'list_count' => 10,
        'enable_notice' => false,
        'is_single_page' => false,
        'enable_sorting' => false,
    ];

    /**
     * 이 게시판에 속한 게시글들 관계
     */
    public function posts()
    {
        return $this->hasMany(BoardPost::class);
    }

    /**
     * 이 게시판에 속한 댓글들 관계
     */
    public function comments()
    {
        return $this->hasManyThrough(
            BoardComment::class,
            BoardPost::class,
            'board_id', // 로컬 키 (boards.id와 연결되는 board_posts 테이블의 외래 키)
            'post_id',  // 원격 키 (board_posts.id와 연결되는 board_comments 테이블의 외래 키)
            'id',       // 로컬 테이블의 기본 키 (boards.id)
            'id'        // 중간 테이블의 기본 키 (board_posts.id)
        );
    }

    /**
     * 이 게시판에 적용된 스킨 관계
     */
    public function skin()
    {
        return $this->belongsTo(BoardSkin::class, 'skin_id');
    }

    /**
     * 이 게시판이 사용하는 템플릿 관계
     */
    public function template()
    {
        return $this->belongsTo(BoardTemplate::class, 'template_id');
    }

    /**
     * 이 게시판과 연결된 메뉴 가져오기 (2차 메뉴에서 찾기)
     */
    public function getAdminMenu()
    {
        $searchPattern = '/backoffice/board-posts/' . $this->slug;
        $menu = AdminMenu::where('url', $searchPattern)
            ->whereNotNull('parent_id') // 2차 메뉴에서 찾기
            ->first();
            
        return $menu;
    }


    /**
     * 이 게시판의 게시글 수 가져오기
     */
    public function getPostsCount(): int
    {
        try {
            $tableName = 'board_' . $this->slug;
            return \Illuminate\Support\Facades\DB::table($tableName)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 이 게시판의 댓글 수 가져오기
     */
    public function getCommentsCount(): int
    {
        try {
            $tableName = 'board_' . $this->slug . '_comments';
            return \Illuminate\Support\Facades\DB::table($tableName)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    
    /**
     * 이 게시판의 커스텀 스킨 뷰 경로를 가져옵니다.
     */
    public function getCustomSkinViewPath($viewType)
    {
        return "boards.instances.{$this->slug}.{$viewType}";
    }
    
    /**
     * 이 게시판이 커스텀 스킨을 가지고 있는지 확인합니다.
     */
    public function hasCustomSkin()
    {
        $customSkinPath = resource_path("views/boards/instances/{$this->slug}");
        return File::exists($customSkinPath);
    }
    
    /**
     * 필드 설정을 안전하게 가져옵니다.
     */
    public function getFieldConfigAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        
        return is_array($value) ? $value : null;
    }

    /**
     * 커스텀 필드 설정을 안전하게 가져옵니다.
     */
    public function getCustomFieldsConfigAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            // 이중 JSON 인코딩된 경우 처리
            $decoded = json_decode($value, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * 특정 필드가 활성화되어 있는지 확인
     */
    public function isFieldEnabled(string $fieldName): bool
    {
        // field_config가 없으면 모든 필드 활성화 (하위 호환성)
        if (!$this->field_config) {
            return true;
        }

        return $this->field_config[$fieldName]['enabled'] ?? false;
    }

    /**
     * 특정 필드가 필수인지 확인
     */
    public function isFieldRequired(string $fieldName): bool
    {
        if (!$this->field_config) {
            // 기본 필수 필드 (제목/내용도 선택 가능)
            return in_array($fieldName, ['author_name']);
        }

        return $this->field_config[$fieldName]['required'] ?? false;
    }

    /**
     * 이 게시판의 스킨 뷰 경로를 가져옵니다 (커스텀 우선, 기본 스킨 대체).
     */
    public function getSkinViewPath($viewType)
    {
        // 커스텀 스킨이 있으면 커스텀 스킨 사용
        if ($this->hasCustomSkin()) {
            return $this->getCustomSkinViewPath($viewType);
        }
        
        // 없으면 기본 스킨 사용
        return "boards.skins.{$this->skin->directory}.{$viewType}";
    }
    
    /**
     * slug가 중복되지 않는지 확인합니다 (소프트 딜리트된 slug는 재사용 가능).
     */
    public static function isSlugAvailable($slug, $excludeId = null)
    {
        // 활성화된(삭제되지 않은) 데이터만 확인하여 소프트 딜리트된 slug는 재사용 가능하도록
        $query = self::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // 활성화된 레코드가 없으면 사용 가능
        return !$query->exists();
    }

    /**
     * 공지 기능이 활성화되어 있는지 확인합니다.
     */
    public function isNoticeEnabled(): bool
    {
        return (bool) $this->enable_notice;
    }

    /**
     * 공지글을 포함한 게시글 목록을 가져옵니다.
     */
    public function getPostsWithNotices($perPage = 15)
    {
        return $this->posts()
            ->orderBy('is_notice', 'desc')  // 공지글이 먼저
            ->orderBy('created_at', 'desc') // 그 다음 최신순
            ->paginate($perPage);
    }

    /**
     * 공지글만 가져옵니다.
     */
    public function getNotices()
    {
        return $this->posts()
            ->where('is_notice', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 일반 게시글만 가져옵니다 (공지글 제외).
     */
    public function getRegularPosts($perPage = 15)
    {
        return $this->posts()
            ->where('is_notice', false)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * 이 게시판의 카테고리 옵션을 가져옵니다
     */
    public function getCategoryOptions()
    {
        // 템플릿에서 선택된 그룹의 모든 하위 카테고리 가져오기
        return $this->template?->getCategoryOptions() ?? collect();
    }
}
