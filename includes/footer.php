
</main>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= ROOT_PATH ?>index.php" class="logo">💼 JobZee</a>
                <p>Connecting talent with opportunity. Find your next great career move.</p>
            </div>
            <div class="footer-links">
                <h4>For Job Seekers</h4>
                <ul>
                    <li><a href="<?= ROOT_PATH ?>jobs.php">Browse Jobs</a></li>
                    <li><a href="<?= ROOT_PATH ?>auth/register.php?role=jobseeker">Create Account</a></li>
                    <li><a href="<?= ROOT_PATH ?>jobseeker/dashboard.php">My Applications</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>For Employers</h4>
                <ul>
                    <li><a href="<?= ROOT_PATH ?>auth/register.php?role=recruiter">Post a Job</a></li>
                    <li><a href="<?= ROOT_PATH ?>recruiter/dashboard.php">Recruiter Dashboard</a></li>
                    <li><a href="<?= ROOT_PATH ?>recruiter/manage_jobs.php">Manage Listings</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> JobZee. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?= ROOT_PATH ?>assets/js/script.js"></script>
</body>
</html>
