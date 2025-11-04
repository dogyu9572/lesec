<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\CreateBoardTemplateRequest;
use App\Http\Requests\Backoffice\UpdateBoardTemplateRequest;
use App\Models\BoardTemplate;
use App\Models\Category;
use App\Services\Backoffice\BoardTemplateService;
use App\Services\Backoffice\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoardTemplateController extends BaseController
{
    protected $templateService;
    protected $categoryService;

    public function __construct(BoardTemplateService $templateService, CategoryService $categoryService)
    {
        $this->templateService = $templateService;
        $this->categoryService = $categoryService;
    }

    /**
     * 템플릿 목록을 표시합니다.
     */
    public function index(Request $request)
    {
        $templates = $this->templateService->getTemplatesWithFilters($request);
        $skins = $this->templateService->getActiveSkins();
        
        return $this->view('backoffice.board-templates.index', compact('templates', 'skins'));
    }

    /**
     * 템플릿 생성 폼을 표시합니다.
     */
    public function create()
    {
        // 그룹 목록(depth=0) 조회
        $categoryGroups = $this->categoryService->getGroups();
        
        $skins = $this->templateService->getActiveSkins();
        return $this->view('backoffice.board-templates.create', compact('skins', 'categoryGroups'));
    }

    /**
     * 새 템플릿을 저장합니다.
     */
    public function store(CreateBoardTemplateRequest $request)
    {
        try {
            $validated = $request->validated();
            $template = $this->templateService->createTemplate($validated);
            
            return redirect()->route('backoffice.board-templates.index')
                ->with('success', '게시판 템플릿이 성공적으로 생성되었습니다.');
                
        } catch (\Exception $e) {
            Log::error('템플릿 생성 실패', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => '템플릿 생성 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * 특정 템플릿 정보를 표시합니다.
     */
    public function show(BoardTemplate $boardTemplate)
    {
        $boardTemplate->load('skin');
        return $this->view('backoffice.board-templates.show', compact('boardTemplate'));
    }

    /**
     * 템플릿 수정 폼을 표시합니다.
     */
    public function edit(BoardTemplate $boardTemplate)
    {
        // 그룹 목록(depth=0) 조회
        $categoryGroups = $this->categoryService->getGroups();
        
        $skins = $this->templateService->getActiveSkins();
        
        // 사용 중인 게시판 정보 조회
        $usedBoards = $boardTemplate->getUsedBoards();
        $isInUse = $boardTemplate->isInUse();
        
        return $this->view('backoffice.board-templates.edit', compact('boardTemplate', 'skins', 'categoryGroups', 'usedBoards', 'isInUse'));
    }

    /**
     * 템플릿 정보를 업데이트합니다.
     */
    public function update(UpdateBoardTemplateRequest $request, BoardTemplate $boardTemplate)
    {
        try {
            $this->templateService->updateTemplate($boardTemplate, $request->validated());
            
            return redirect()->route('backoffice.board-templates.index')
                ->with('success', '템플릿이 성공적으로 수정되었습니다.');
                
        } catch (\Exception $e) {
            Log::error('템플릿 수정 실패', [
                'template_id' => $boardTemplate->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => '템플릿 수정 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * 템플릿을 삭제합니다.
     */
    public function destroy(BoardTemplate $boardTemplate)
    {
        try {
            // 사용 중인 템플릿 삭제 방지
            if ($boardTemplate->boards()->count() > 0) {
                return redirect()->back()
                    ->withErrors(['error' => '사용 중인 템플릿은 삭제할 수 없습니다. (' . $boardTemplate->boards()->count() . '개 게시판에서 사용 중)']);
            }
            
            $this->templateService->deleteTemplate($boardTemplate);
            
            return redirect()->route('backoffice.board-templates.index')
                ->with('success', '템플릿이 성공적으로 삭제되었습니다.');
                
        } catch (\Exception $e) {
            Log::error('템플릿 삭제 실패', [
                'template_id' => $boardTemplate->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => '템플릿 삭제 중 오류가 발생했습니다.']);
        }
    }

    /**
     * 템플릿을 복제합니다.
     */
    public function duplicate(BoardTemplate $boardTemplate)
    {
        try {
            $newTemplate = $this->templateService->duplicateTemplate($boardTemplate);
            
            return redirect()->route('backoffice.board-templates.edit', $newTemplate)
                ->with('success', '템플릿이 성공적으로 복제되었습니다. 필요한 내용을 수정해주세요.');
                
        } catch (\Exception $e) {
            Log::error('템플릿 복제 실패', [
                'template_id' => $boardTemplate->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => '템플릿 복제 중 오류가 발생했습니다.']);
        }
    }

    /**
     * 템플릿 정보를 JSON으로 반환합니다. (AJAX용)
     */
    public function getTemplateData(BoardTemplate $boardTemplate)
    {
        return response()->json([
            'success' => true,
            'data' => $boardTemplate->load('skin')
        ]);
    }
}
