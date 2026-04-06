<!-- Manage Attempts Modal - Compact Version -->
<div class="modal fade" id="manageAttemptsModal" tabindex="-1" aria-labelledby="manageAttemptsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded">
            <div class="modal-header border-0" style="background-color: #001A33;">
                <h6 class="modal-title mb-0 text-white" id="manageAttemptsModalLabel">
                    <i class="ti ti-adjustments me-1 text-white opacity-75"></i> Manage Attempts
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <form id="manageAttemptsForm">
                    @csrf
                    <input type="hidden" id="studentId" name="student_id">

                    <!-- Student Name -->
                    <div class="mb-3">
                        <small class="text-muted">Student:</small>
                        <div class="fw-semibold" id="studentName"></div>
                    </div>

                    <!-- Select Exam -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Select Exam</label>
                        <select class="form-select form-select-sm" id="examSelect" name="exam_id" required>
                            <option value="">Choose exam...</option>
                            <!-- Options will be populated dynamically based on user's assigned exams -->
                        </select>
                    </div>

                    <!-- Current Stats -->
                    <div id="currentAttemptsInfo" class="d-none mb-3">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">Total</small>
                                <strong class="text-primary" id="currentAttemptsAllowed">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Used</small>
                                <strong class="text-secondary" id="currentAttemptsUsed">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Left</small>
                                <strong class="text-success" id="currentAttemptsRemaining">0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Precise Adjustment -->
                    <div class="mb-4 text-center">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-3"
                            style="letter-spacing: 0.5px; font-size: 0.7rem;">Adjustment Amount</label>
                        <div class="d-flex align-items-center justify-content-between bg-light rounded-pill border mx-auto px-2"
                            style="max-width: 240px; height: 54px; border-color: #e2e8f0 !important;">
                            <button type="button"
                                class="btn btn-link no-debounce rounded-circle d-flex align-items-center justify-content-center p-0 shadow-none text-decoration-none"
                                onclick="adjustAttempts(-1)"
                                style="width: 38px; height: 38px; transition: all 0.2s; border: none; outline: none;">
                                <i class="ti ti-minus fs-3 text-danger opacity-75"></i>
                            </button>
                            <div class="flex-grow-1">
                                <input type="number"
                                    class="form-control form-control-lg text-center fw-bold border-0 bg-transparent p-0 shadow-none"
                                    id="attemptsAdjustment" value="0" readonly
                                    style="font-size: 1.5rem; color: #334155; pointer-events: none;">
                            </div>
                            <button type="button"
                                class="btn btn-link no-debounce rounded-circle d-flex align-items-center justify-content-center p-0 shadow-none text-decoration-none"
                                onclick="adjustAttempts(1)"
                                style="width: 38px; height: 38px; transition: all 0.2s; border: none; outline: none;">
                                <i class="ti ti-plus fs-3 text-success opacity-75"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Impact Preview -->
                    <div id="newAttemptsPreview" class="d-none">
                        <div class="card bg-light border-0 shadow-none mb-0"
                            style="background-color: #f8fafc !important;">
                            <div class="card-body p-3">
                                <div class="row align-items-center text-center">
                                    <div class="col-6 border-end" style="border-color: #e2e8f0 !important;">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Total
                                            Attempts</small>
                                        <h4 class="mb-0 text-primary fw-bold" id="newAttemptsTotal">0</h4>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Attempts
                                            Left</small>
                                        <h4 class="mb-0 text-success fw-bold" id="newAttemptsRemaining">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer p-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveAttempts()">
                    <i class="ti ti-check me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentStudentId = null;
    let currentExamId = null;
    let currentAttemptsData = {};

    function openManageAttemptsModal(studentId, studentName, studentEmail) {
        currentStudentId = studentId;
        document.getElementById('studentId').value = studentId;
        document.getElementById('studentName').textContent = studentName;
        document.getElementById('attemptsAdjustment').value = 0;
        document.getElementById('currentAttemptsInfo').classList.add('d-none');
        document.getElementById('newAttemptsPreview').classList.add('d-none');

        // Show modal programmatically
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageAttemptsModal'));
        modal.show();

        // Clear and populate exam dropdown with only assigned exams
        const examSelect = document.getElementById('examSelect');
        examSelect.innerHTML = '<option value="">Loading...</option>';

        // Fetch user's assigned exams
        fetch(`/admin/users/${studentId}/assigned-exams`)
            .then(response => response.json())
            .then(data => {
                examSelect.innerHTML = '<option value="">Choose exam...</option>';

                if (data.success && data.exams.length > 0) {
                    data.exams.forEach(exam => {
                        const option = document.createElement('option');
                        option.value = exam.exam_id;
                        option.textContent = exam.exam_name;
                        examSelect.appendChild(option);
                    });
                } else {
                    examSelect.innerHTML = '<option value="">No exams assigned</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                examSelect.innerHTML = '<option value="">Error loading exams</option>';
            });
    }

    // When exam is selected, fetch current attempts
    document.getElementById('examSelect').addEventListener('change', function () {
        const examId = this.value;
        currentExamId = examId;

        if (!examId || !currentStudentId) {
            document.getElementById('currentAttemptsInfo').classList.add('d-none');
            document.getElementById('newAttemptsPreview').classList.add('d-none');
            return;
        }

        // Fetch current attempts via AJAX
        fetch(`/admin/users/${currentStudentId}/exam/${examId}/attempts`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentAttemptsData = data.data;
                    document.getElementById('currentAttemptsAllowed').textContent = data.data.attempts_allowed;
                    document.getElementById('currentAttemptsUsed').textContent = data.data.attempts_used;
                    document.getElementById('currentAttemptsRemaining').textContent = data.data.attempts_remaining;
                    document.getElementById('currentAttemptsInfo').classList.remove('d-none');
                    document.getElementById('attemptsAdjustment').value = 0;
                    updatePreview();
                } else {
                    // Exam not assigned yet
                    currentAttemptsData = {
                        attempts_allowed: 0,
                        attempts_used: 0,
                        attempts_remaining: 0,
                        is_assigned: false
                    };
                    document.getElementById('currentAttemptsInfo').classList.add('d-none');
                    document.getElementById('attemptsAdjustment').value = 0;
                    alert('This exam is not assigned to this student yet. You can assign it by adding attempts.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to fetch current attempts');
            });
    });

    function adjustAttempts(change) {
        const examSelect = document.getElementById('examSelect');
        if (!examSelect.value) {
            if (typeof showAlert !== 'undefined') {
                showAlert.warning('Please select an exam first');
            } else {
                alert('Please select an exam first');
            }
            return;
        }

        const input = document.getElementById('attemptsAdjustment');
        let currentValue = parseInt(input.value) || 0;

        // Calculate potential new total
        const currentAllowed = currentAttemptsData.attempts_allowed || 0;
        const potentialTotal = currentAllowed + currentValue + change;

        // Prevent negative total logic
        if (potentialTotal < 0) {
            return;
        }

        input.value = currentValue + change;
        updatePreview();
    }

    function updatePreview() {
        const adjustment = parseInt(document.getElementById('attemptsAdjustment').value) || 0;
        const currentAllowed = currentAttemptsData.attempts_allowed || 0;
        const currentUsed = currentAttemptsData.attempts_used || 0;

        const newTotal = currentAllowed + adjustment;
        const newRemaining = newTotal - currentUsed;

        if (adjustment !== 0) {
            document.getElementById('newAttemptsTotal').textContent = newTotal;
            document.getElementById('newAttemptsRemaining').textContent = newRemaining;
            document.getElementById('newAttemptsPreview').classList.remove('d-none');
        } else {
            document.getElementById('newAttemptsPreview').classList.add('d-none');
        }
    }

    function saveAttempts() {
        const studentId = document.getElementById('studentId').value;
        const examId = document.getElementById('examSelect').value;
        const adjustment = parseInt(document.getElementById('attemptsAdjustment').value) || 0;

        if (!examId) {
            if (typeof showAlert !== 'undefined') showAlert.warning('Please select an exam first');
            return;
        }

        if (adjustment === 0) {
            if (typeof showAlert !== 'undefined') showAlert.warning('Please adjust the attempts using + or - buttons');
            return;
        }

        const saveBtn = document.querySelector('button[onclick="saveAttempts()"]');
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        // Send AJAX request to update attempts
        fetch('/admin/users/manage-attempts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                student_id: studentId,
                exam_id: examId,
                attempts_adjustment: adjustment
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal first
                    const modal = bootstrap.Modal.getInstance(document.getElementById('manageAttemptsModal'));
                    if (modal) modal.hide();

                    if (typeof showAlert !== 'undefined') {
                        showAlert.success(data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        alert(data.message);
                        location.reload();
                    }
                } else {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalHtml;
                    if (typeof showAlert !== 'undefined') {
                        showAlert.error(data.message);
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
                if (typeof showAlert !== 'undefined') {
                    showAlert.error('Failed to update attempts. Please try again.');
                } else {
                    alert('Failed to update attempts');
                }
            });
    }
</script>