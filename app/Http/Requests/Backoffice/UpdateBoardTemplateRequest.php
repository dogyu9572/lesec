<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardTemplateRequest extends FormRequest
{
    /**
     * 사용자가 이 요청을 할 권한이 있는지 확인
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙
     */
    public function rules(): array
    {
        $template = $this->route('board_template');
        
        // 시스템 템플릿인 경우 활성화 여부만 수정 가능
        if ($template && $template->is_system) {
            return [
                'is_active' => 'nullable|boolean',
            ];
        }
        
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skin_id' => 'required|exists:board_skins,id',
            
            // 필드 설정
            'field_title_enabled' => 'nullable|boolean',
            'field_title_required' => 'nullable|boolean',
            'field_title_label' => 'nullable|string|max:50',
            
            'field_content_enabled' => 'nullable|boolean',
            'field_content_required' => 'nullable|boolean',
            'field_content_label' => 'nullable|string|max:50',
            
            'field_category_enabled' => 'nullable|boolean',
            'field_category_required' => 'nullable|boolean',
            'field_category_label' => 'nullable|string|max:50',
            
            'field_author_name_enabled' => 'nullable|boolean',
            'field_author_name_required' => 'nullable|boolean',
            'field_author_name_label' => 'nullable|string|max:50',
            
            'field_password_enabled' => 'nullable|boolean',
            'field_password_required' => 'nullable|boolean',
            'field_password_label' => 'nullable|string|max:50',
            
            'field_attachments_enabled' => 'nullable|boolean',
            'field_attachments_required' => 'nullable|boolean',
            'field_attachments_label' => 'nullable|string|max:50',
            
            'field_thumbnail_enabled' => 'nullable|boolean',
            'field_thumbnail_required' => 'nullable|boolean',
            'field_thumbnail_label' => 'nullable|string|max:50',
            
            'field_is_secret_enabled' => 'nullable|boolean',
            'field_is_secret_required' => 'nullable|boolean',
            'field_is_secret_label' => 'nullable|string|max:50',
            
            'field_created_at_enabled' => 'nullable|boolean',
            'field_created_at_required' => 'nullable|boolean',
            'field_created_at_label' => 'nullable|string|max:50',
            
            // 커스텀 필드
            'custom_fields' => 'nullable|array',
            'custom_fields.*.name' => 'required_with:custom_fields|string|max:50',
            'custom_fields.*.label' => 'required_with:custom_fields|string|max:100',
            'custom_fields.*.type' => 'required_with:custom_fields|in:text,textarea,number,date,select,radio,checkbox',
            'custom_fields.*.max_length' => 'nullable|integer|min:1',
            'custom_fields.*.required' => 'nullable|boolean',
            'custom_fields.*.options' => 'nullable|string',
            'custom_fields.*.placeholder' => 'nullable|string|max:100',
            
            // 기능 설정
            'enable_notice' => 'nullable|boolean',
            'enable_sorting' => 'nullable|boolean',
            'enable_category' => 'nullable|boolean',
            'category_group' => 'nullable|string|max:50',
            
            // 목록 및 권한 설정
            'list_count' => 'nullable|integer|min:5|max:100',
            'permission_read' => 'nullable|in:all,member,admin',
            'permission_write' => 'nullable|in:all,member,admin',
            'permission_comment' => 'nullable|in:all,member,admin',
            
            // 시스템 설정
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * 유효성 검사 오류 메시지
     */
    public function messages(): array
    {
        return [
            'name.required' => '템플릿 이름을 입력해주세요.',
            'name.max' => '템플릿 이름은 255자를 초과할 수 없습니다.',
            'skin_id.required' => '스킨을 선택해주세요.',
            'skin_id.exists' => '선택한 스킨이 존재하지 않습니다.',
            'list_count.min' => '목록 개수는 최소 5개입니다.',
            'list_count.max' => '목록 개수는 최대 100개입니다.',
            'custom_fields.*.name.required_with' => '커스텀 필드명을 입력해주세요.',
            'custom_fields.*.label.required_with' => '커스텀 필드 라벨을 입력해주세요.',
            'custom_fields.*.type.required_with' => '커스텀 필드 타입을 선택해주세요.',
            'custom_fields.*.type.in' => '유효하지 않은 필드 타입입니다.',
        ];
    }
}
