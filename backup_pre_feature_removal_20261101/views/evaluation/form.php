<!-- views/evaluation/form.php -->
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-clipboard-list"></i> Thesis Evaluation Survey Instrument (TAM & ISO 9126)</div>
    </div>
    <div class="card-body">
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px;">
            This survey evaluates the software quality and user acceptance of the <em>Intelligent Resource Allocation Recommender for Barangay Disaster Preparedness Activities</em> based on the Technology Acceptance Model (TAM) and ISO 9126 software quality model (St. Peter's College, May 2026).
            <br><strong>Scale:</strong> 5 – Strongly Agree | 4 – Agree | 3 – Neutral | 2 – Disagree | 1 – Strongly Disagree
        </p>

        <form action="index.php?route=evaluation-submit" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Respondent Stakeholder Role *</label>
                <select name="respondent_type" class="form-control" required>
                    <option value="Barangay Official">Barangay Official involved in DRRM</option>
                    <option value="BDRRM Head">Barangay DRRM Head</option>
                    <option value="Personnel/Volunteer">Disaster Management Personnel / Volunteer / Rescuer</option>
                    <option value="Resident">Resident participating in preparedness activities</option>
                </select>
            </div>

            <!-- PART 1: TAM Perceived Usefulness (PU) -->
            <h3 style="font-size: 1.05rem; color: var(--primary-dark); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid var(--primary-light);">
                Part I: Technology Acceptance Model — Perceived Usefulness (PU)
            </h3>

            <div class="form-group">
                <label class="form-label">1. Using the system improves the efficiency and speed of resource allocation planning for disaster preparedness.</label>
                <select name="pu_q1" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">2. The Decision Tree recommendations provide objective, transparent guidance on which puroks and needs should be prioritized.</label>
                <select name="pu_q2" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">3. Centralized inventory tracking prevents shortages and over-allocation during disaster preparedness activities.</label>
                <select name="pu_q3" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">4. Overall, I find the Intelligent Resource Allocation Recommender useful for Barangay Hinaplanon disaster operations.</label>
                <select name="pu_q4" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <!-- PART 2: TAM Perceived Ease of Use (PEOU) -->
            <h3 style="font-size: 1.05rem; color: var(--primary-dark); margin: 24px 0 12px 0; padding-bottom: 6px; border-bottom: 2px solid var(--primary-light);">
                Part II: Technology Acceptance Model — Perceived Ease of Use (PEOU)
            </h3>

            <div class="form-group">
                <label class="form-label">1. Learning to operate the web-based disaster recommender system was clear and straightforward.</label>
                <select name="peou_q1" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">2. Navigation between modules (disaster requests, inventory, hazard map, calendar) is intuitive and easy to follow.</label>
                <select name="peou_q2" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">3. The recommendation explanations and decision paths are easily understood by non-technical barangay personnel.</label>
                <select name="peou_q3" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">4. Overall, the system does not require burdensome mental effort to accomplish disaster planning tasks.</label>
                <select name="peou_q4" class="form-control" required>
                    <option value="5">5 – Strongly Agree</option><option value="4">4 – Agree</option><option value="3">3 – Neutral</option><option value="2">2 – Disagree</option><option value="1">1 – Strongly Disagree</option>
                </select>
            </div>

            <!-- PART 3: ISO 9126 Software Quality Criteria -->
            <h3 style="font-size: 1.05rem; color: var(--primary-dark); margin: 24px 0 12px 0; padding-bottom: 6px; border-bottom: 2px solid var(--primary-light);">
                Part III: ISO 9126 Software Quality Model Criteria
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">3.1 Functionality (Meets stated DRRM needs)</label>
                    <select name="iso_functionality" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3.2 Reliability (Consistency & error prevention)</label>
                    <select name="iso_reliability" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3.3 Usability (Interface layout & clarity)</label>
                    <select name="iso_usability" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3.4 Efficiency (Fast response & load speed)</label>
                    <select name="iso_efficiency" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3.5 Maintainability (Modular architecture)</label>
                    <select name="iso_maintainability" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3.6 Portability (Multi-device accessibility)</label>
                    <select name="iso_portability" class="form-control" required>
                        <option value="5">5 – Excellent</option><option value="4">4 – Very Good</option><option value="3">3 – Satisfactory</option><option value="2">2 – Fair</option><option value="1">1 – Poor</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px;">
                <label class="form-label">Qualitative Comments & Suggestions for System Improvement:</label>
                <textarea name="qualitative_feedback" class="form-control" rows="3" placeholder="Provide any additional comments or observations..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    <i class="fa fa-paper-plane"></i> Submit Evaluation Response
                </button>
                <a href="index.php?route=evaluation-results" class="btn btn-outline">View Statistical Dashboard</a>
            </div>
        </form>
    </div>
</div>
