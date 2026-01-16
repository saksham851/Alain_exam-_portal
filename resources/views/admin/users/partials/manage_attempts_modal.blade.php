<!-- Manage Attempts Modal - Compact Version -->
<div class="modal fade" id="manageAttemptsModal" tabindex="-1" aria-labelledby="manageAttemptsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 rounded">
            <div class="modal-header bg-primary border-0">
                <h6 class="modal-title mb-0 text-white" id="manageAttemptsModalLabel">
                    <i class="ti ti-adjustments me-1 text-white"></i> Manage Attempts
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

                    <!-- Adjust -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Adjust</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="adjustAttempts(-1)">
                                <i class="ti ti-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center fw-bold" 
                                   id="attemptsAdjustment" value="0" readonly>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="adjustAttempts(1)">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="newAttemptsPreview" class="d-none">
                        <div class="alert alert-success alert-sm py-2 mb-0">
                            <small><strong>New Total:</strong></small>
                            <span id="newAttemptsTotal" class="badge bg-success ms-2">0</span>
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
document.getElementById('examSelect').addEventListener('change', function() {
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
    const input = document.getElementById('attemptsAdjustment');
    let currentValue = parseInt(input.value) || 0;
    
    // Calculate potential new total
    const currentAllowed = currentAttemptsData.attempts_allowed || 0;
    const potentialTotal = currentAllowed + currentValue + change;
    
    // Prevent negative total logic
    if (potentialTotal < 0) {
        // Cannot reduce below zero
        return; 
    }
    
    input.value = currentValue + change;
    updatePreview();
}

function updatePreview() {
    const adjustment = parseInt(document.getElementById('attemptsAdjustment').value) || 0;
    const currentAllowed = currentAttemptsData.attempts_allowed || 0;
    const newTotal = currentAllowed + adjustment;
    
    if (adjustment !== 0) {
        document.getElementById('newAttemptsTotal').textContent = newTotal;
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
        showAlert.warning('Please select an exam first');
        return;
    }

    if (adjustment === 0) {
        showAlert.warning('Please adjust the attempts using + or - buttons');
        return;
    }

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
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('manageAttemptsModal'));
        modal.hide();
        
        // Wait for modal to close, then show alert
        setTimeout(() => {
            if (data.success) {
                showAlert.success(data.message);
                // Reload after alert shows
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showAlert.error(data.message);
            }
        }, 300);
    })
    .catch(error => {
        console.error('Error:', error);
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('manageAttemptsModal'));
        modal.hide();
        
        // Show error after modal closes
        setTimeout(() => {
            showAlert.error('Failed to update attempts. Please try again.');
        }, 300);
    });
}
</script>
