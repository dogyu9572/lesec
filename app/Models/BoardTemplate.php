<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'skin_id',
        'field_config',
        'custom_fields_config',
        'enable_notice',
        'enable_sorting',
        'enable_category',
        'category_group',
        'list_count',
        'permission_read',
        'permission_write',
        'permission_comment',
        'is_single_page',
        'is_active',
    ];

    protected $casts = [
        'field_config' => 'array',
        'custom_fields_config' => 'array',
        'enable_notice' => 'boolean',
        'enable_sorting' => 'boolean',
        'enable_category' => 'boolean',
        'is_single_page' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'enable_notice' => true,
        'enable_sorting' => false,
        'enable_category' => true,
        'list_count' => 15,
        'permission_read' => 'all',
        'permission_write' => 'member',
        'permission_comment' => 'member',
        'is_active' => true,
    ];

    /**
     * 템플릿에 연결된 스킨
     */
    public function skin()
    {
        return $this->belongsTo(BoardSkin::class, 'skin_id');
    }

    /**
     * 이 템플릿을 사용하는 게시판들
     */
    public function boards()
    {
        return $this->hasMany(Board::class, 'template_id');
    }

    /**
     * 필드 설정을 안전하게 가져옵니다.
     */
    public function getFieldConfigAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : $this->getDefaultFieldConfig();
        }
        
        return is_array($value) ? $value : $this->getDefaultFieldConfig();
    }

    /**
     * 커스텀 필드 설정을 안전하게 가져옵니다.
     */
    public function getCustomFieldsConfigAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * 기본 필드 설정을 반환합니다.
     */
    public function getDefaultFieldConfig(): array
    {
        return [
            'title' => ['enabled' => true, 'required' => true, 'label' => '제목'],
            'content' => ['enabled' => true, 'required' => true, 'label' => '내용'],
            'category' => ['enabled' => true, 'required' => false, 'label' => '카테고리'],
            'author_name' => ['enabled' => true, 'required' => true, 'label' => '작성자'],
            'password' => ['enabled' => true, 'required' => false, 'label' => '비밀번호'],
            'attachments' => ['enabled' => true, 'required' => false, 'label' => '첨부파일'],
            'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
            'is_secret' => ['enabled' => true, 'required' => false, 'label' => '비밀글'],
        ];
    }

    /**
     * 활성화된 템플릿만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 이 템플릿을 사용하는 게시판 수를 반환합니다.
     */
    public function getUsedBoardCount(): int
    {
        return $this->boards()->count();
    }

    /**
     * 이 템플릿이 사용 중인지 확인합니다.
     */
    public function isInUse(): bool
    {
        return $this->getUsedBoardCount() > 0;
    }

    /**
     * 이 템플릿을 사용하는 게시판 목록을 반환합니다.
     */
    public function getUsedBoards()
    {
        return $this->boards()->get();
    }
}

