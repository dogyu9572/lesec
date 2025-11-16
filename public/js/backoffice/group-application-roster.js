document.addEventListener('DOMContentLoaded', function() {
    const cfg = document.getElementById('roster-config');
    if (!cfg) return;
    const appId = Number(cfg.getAttribute('data-application-id') || '0');
    const uploadUrl = cfg.getAttribute('data-upload-url') || '';
    const sampleUrl = cfg.getAttribute('data-sample-url') || '';
    const storeUrl = cfg.getAttribute('data-participant-store-url') || '';
    const csrf = cfg.getAttribute('data-csrf-token') || '';

    // 샘플 다운로드는 링크로 처리돼서 JS 필요 없음

    // 일괄 업로드
    const uploadBtn = document.getElementById('btn-admin-upload');
    const fileInput = document.getElementById('admin_csv_upload');
    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener('click', function() {
            fileInput.click();
        });
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file || !uploadUrl) return;
            const formData = new FormData();
            formData.append('csv_file', file);
            fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                body: formData
            })
            .then(res => res.json())
            .then(json => {
                if (json && json.success) {
                    alert(json.message || '업로드가 완료되었습니다.');
                    location.reload();
                } else {
                    alert((json && json.message) || '업로드 중 오류가 발생했습니다.');
                }
            })
            .catch(() => alert('업로드 중 오류가 발생했습니다.'));
        });
    }

    // 추가 행 생성 및 저장
    const addBtn = document.getElementById('btn-admin-add-row');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const tbody = document.querySelector('.board-table tbody');
            if (!tbody) return;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>+</td>
                <td><input type="text" class="form-control" data-field="name" placeholder="이름"></td>
                <td>
                    <select class="form-control" data-field="grade">
                        <option value="">학년</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </td>
                <td>
                    <select class="form-control" data-field="class">
                        <option value="">반</option>
                        ${Array.from({length: 20}, (_, i) => `<option value="${i+1}">${i+1}</option>`).join('')}
                    </select>
                </td>
                <td><input type="date" class="form-control" data-field="birthday" placeholder="YYYY-MM-DD"></td>
                <td>
                    <div class="board-btn-group">
                        <button type="button" class="btn btn-primary btn-sm" data-action="save">저장</button>
                        <button type="button" class="btn btn-danger btn-sm" data-action="cancel">취소</button>
                    </div>
                </td>
            `;
            tbody.prepend(tr);

            const onCancel = tr.querySelector('[data-action="cancel"]');
            const onSave = tr.querySelector('[data-action="save"]');

            onCancel.addEventListener('click', function() {
                tr.remove();
            });

            onSave.addEventListener('click', function() {
                const payload = {
                    name: (tr.querySelector('[data-field="name"]').value || '').trim(),
                    grade: tr.querySelector('[data-field="grade"]').value,
                    class: tr.querySelector('[data-field="class"]').value,
                    birthday: (tr.querySelector('[data-field="birthday"]').value || '').trim(),
                };
                if (!payload.name || !payload.grade || !payload.class) {
                    alert('이름, 학년, 반은 필수입니다.');
                    return;
                }
                fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(json => {
                    if (json && json.success) {
                        alert('저장되었습니다.');
                        location.reload();
                    } else {
                        alert((json && json.message) || '저장 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('저장 중 오류가 발생했습니다.'));
            });
        });
    }

    // 기존 행 수정 / 삭제 (이벤트 위임)
    const table = document.querySelector('.board-table tbody');
    if (table) {
        table.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;
            const tr = btn.closest('tr');
            const action = btn.getAttribute('data-action');
            const pid = tr.getAttribute('data-participant-id');
            if (!pid) return;

            if (action === 'delete') {
                if (!confirm('해당 참가자를 삭제하시겠습니까?')) return;
                const url = storeUrl + '/' + pid;
                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf }
                })
                .then(res => res.json())
                .then(json => {
                    if (json && json.success) {
                        alert('삭제되었습니다.');
                        location.reload();
                    } else {
                        alert((json && json.message) || '삭제 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('삭제 중 오류가 발생했습니다.'));
                return;
            }

            if (action === 'edit') {
                // 편집 모드로 전환
                const nameTd = tr.querySelector('.pv-name');
                const gradeTd = tr.querySelector('.pv-grade');
                const classTd = tr.querySelector('.pv-class');
                const birthTd = tr.querySelector('.pv-birthday');
                const orig = {
                    name: nameTd.textContent.trim() === '-' ? '' : nameTd.textContent.trim(),
                    grade: gradeTd.textContent.trim() === '-' ? '' : gradeTd.textContent.trim(),
                    class: classTd.textContent.trim() === '-' ? '' : classTd.textContent.trim(),
                    birthday: birthTd.textContent.trim() === '-' ? '' : birthTd.textContent.trim(),
                };
                nameTd.innerHTML = `<input type="text" class="form-control" data-edit="name" value="${orig.name}">`;
                gradeTd.innerHTML = `
                    <select class="form-control" data-edit="grade">
                        <option value="">학년</option>
                        <option value="1"${orig.grade==='1'?' selected':''}>1</option>
                        <option value="2"${orig.grade==='2'?' selected':''}>2</option>
                        <option value="3"${orig.grade==='3'?' selected':''}>3</option>
                    </select>`;
                classTd.innerHTML = `
                    <select class="form-control" data-edit="class">
                        <option value="">반</option>
                        ${Array.from({length: 20}, (_, i) => {
                            const v = String(i+1);
                            return `<option value="${v}"${orig.class===v?' selected':''}>${v}</option>`;
                        }).join('')}
                    </select>`;
                birthTd.innerHTML = `<input type="date" class="form-control" data-edit="birthday" value="${orig.birthday}">`;

                const btnGroup = tr.querySelector('.board-btn-group');
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-primary btn-sm" data-action="save-edit">저장</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-action="cancel-edit">취소</button>
                `;
                return;
            }

            if (action === 'cancel-edit') {
                location.reload();
                return;
            }

            if (action === 'save-edit') {
                const payload = {
                    name: tr.querySelector('[data-edit="name"]').value.trim(),
                    grade: tr.querySelector('[data-edit="grade"]').value,
                    class: tr.querySelector('[data-edit="class"]').value,
                    birthday: tr.querySelector('[data-edit="birthday"]').value.trim(),
                };
                if (!payload.name || !payload.grade || !payload.class) {
                    alert('이름, 학년, 반은 필수입니다.');
                    return;
                }
                const url = storeUrl + '/' + pid;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(json => {
                    if (json && json.success) {
                        alert('수정되었습니다.');
                        location.reload();
                    } else {
                        alert((json && json.message) || '수정 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('수정 중 오류가 발생했습니다.'));
            }
        });
    }
});


