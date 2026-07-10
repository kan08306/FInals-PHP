    </main>

    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-brand">
                <div class="footer-brand-lockup">
                    <img src="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>assets/images/logos/logonnmae.png" alt="Shenanovents logo">
                </div>

                <div class="footer-tagline">
                    <p>Bringing People Together Through Events.</p>
                    <p>Built for Every Event, Designed for Every Experience.</p>
                </div>

                <div class="social-links" aria-label="Social links">
                    <a href="#" aria-label="Facebook"><img src="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>assets/images/icons/facebook.svg" alt=""></a>
                    <a href="#" aria-label="X"><img src="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>assets/images/icons/x-twitter.svg" alt=""></a>
                    <a href="#" aria-label="Instagram"><img src="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>assets/images/icons/instagram.svg" alt=""></a>
                </div>
            </div>

            <div class="footer-links">
                <div>
                    <h2>Resources</h2>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>faq.php">FAQ</a>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>help-center.php">Help Center</a>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>contact-support.php">Contact Support</a>
                </div>

                <div>
                    <h2>Company</h2>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>about-us.php">About Us</a>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>privacy-policy.php">Privacy Policy</a>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>terms-of-service.php">Terms of Service</a>
                    <a href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>contact.php">Contact</a>
                </div>
            </div>
        </div>

        <p class="copyright">&copy; 2026 Shenanovents. All Rights Reserved.</p>
    </footer>

    <div class="registration-modal-overlay" data-auth-required-modal aria-hidden="true" hidden>
        <section class="registration-modal-card auth-required-card" role="dialog" aria-modal="true" aria-labelledby="authRequiredTitle">
            <button class="registration-modal-close" type="button" aria-label="Close popup" data-modal-close>&times;</button>
            <span class="registration-modal-accent" aria-hidden="true"></span>
            <p class="registration-modal-kicker">Sign in required</p>
            <h2 id="authRequiredTitle" data-auth-required-title>Please sign in first</h2>
            <p data-auth-required-message>Please sign in to register for this event.</p>
            <div class="registration-modal-actions">
                <a class="button button-primary" href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>auth/signin.php">Sign In</a>
                <button class="button button-outline" type="button" data-modal-close>Cancel</button>
            </div>
        </section>
    </div>

    <div class="registration-modal-overlay" data-registration-modal aria-hidden="true" hidden>
        <section class="registration-modal-card event-registration-card" role="dialog" aria-modal="true" aria-labelledby="registrationModalTitle">
            <button class="registration-modal-close" type="button" aria-label="Close registration form" data-modal-close>&times;</button>

            <form class="event-registration-form" action="" method="post" data-registration-form>
                <input type="hidden" name="participant_action" value="register_event">
                <input type="hidden" name="event_id" value="" data-registration-event-id>

                <div class="registration-modal-heading">
                    <span class="registration-free-badge">Free Registration</span>
                    <h2 id="registrationModalTitle" data-registration-title>Event Registration</h2>
                    <div class="registration-event-details" aria-label="Selected event details">
                        <span><strong>Date</strong><small data-registration-date>Event date</small></span>
                        <span><strong>Time</strong><small data-registration-time>Event time</small></span>
                        <span><strong>Location</strong><small data-registration-location>Event location</small></span>
                    </div>
                </div>

                <div class="registration-field-grid">
                    <label>
                        <span>Full Name</span>
                        <input type="text" name="full_name" placeholder="Enter your full name" required>
                    </label>
                    <label>
                        <span>Email Address</span>
                        <input type="email" name="email" placeholder="Enter your email address" required>
                    </label>
                    <label>
                        <span>Contact Number</span>
                        <input type="tel" name="contact_number" placeholder="Enter your contact number" required>
                    </label>
                    <label>
                        <span>Number of Attendees</span>
                        <input type="number" name="attendees" min="1" value="1" required>
                    </label>
                    <label class="registration-field-full">
                        <span>Special Notes or Requests</span>
                        <textarea name="notes" rows="4" placeholder="Add notes or requests for the organizer"></textarea>
                    </label>
                </div>

                <label class="registration-confirm-row">
                    <input type="checkbox" name="registration_confirm" value="1" data-registration-confirm>
                    <span>I confirm that the information I provided is correct.</span>
                </label>

                <div class="registration-modal-actions">
                    <button class="button button-primary" type="submit" data-registration-submit disabled>Register</button>
                    <button class="button button-outline" type="button" data-modal-close>Cancel</button>
                </div>
            </form>

            <div class="registration-success-state" data-registration-success hidden>
                <span class="registration-success-icon" aria-hidden="true"></span>
                <h2>Registration successful</h2>
                <p>Registration successful. Your ticket has been added to your Tickets page.</p>
                <div class="registration-modal-actions">
                    <a class="button button-primary" href="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>participant/tickets.php">View Tickets</a>
                    <button class="button button-outline" type="button" data-modal-close>Close</button>
                </div>
            </div>
        </section>
    </div>

    <?php
    $private_event_error = $_SESSION['private_event_error'] ?? '';
    unset($_SESSION['private_event_error']);
    $private_event_return = $_SERVER['REQUEST_URI'] ?? (($base_path ?? '') . 'participant/events.php');
    ?>
    <div class="registration-modal-overlay" data-private-event-modal aria-hidden="true" hidden>
        <section class="registration-modal-card private-event-card" role="dialog" aria-modal="true" aria-labelledby="privateEventTitle">
            <button class="registration-modal-close" type="button" aria-label="Close private event access" data-modal-close>&times;</button>
            <span class="registration-modal-accent" aria-hidden="true"></span>
            <p class="registration-modal-kicker">Invitation only</p>
            <h2 id="privateEventTitle">Private Event Access</h2>
            <p>Enter your private event code to open your invitation.</p>

            <?php if ($private_event_error !== ''): ?>
                <p class="private-event-error" role="alert"><?php echo htmlspecialchars($private_event_error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form class="private-event-form" method="post" action="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>participant/private-event-access.php">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($private_event_return, ENT_QUOTES, 'UTF-8'); ?>">
                <label>
                    <span>Private event code</span>
                    <input type="text" name="private_event_code" placeholder="PRIVATE-SHNV-8F3K2A" autocomplete="off" required>
                </label>
                <div class="registration-modal-actions">
                    <button class="button button-primary" type="submit">Access Event</button>
                </div>
            </form>
        </section>
    </div>

    <script src="<?php echo htmlspecialchars($base_path ?? '', ENT_QUOTES, 'UTF-8'); ?>assets/js/main.js?v=<?php echo htmlspecialchars($asset_version ?? 'frontend-standards', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>

