<script type="text/javascript" charset="utf-8" src="/js/userChallenge.js"></script>
<section class="admin-message" id="user-challenge"
  data-delay-seconds="<?php echo $delaySeconds; ?>"
  data-cookie-name-has-js="<?php echo $cookieNameHasJs; ?>"
  data-cookie-value="<?php echo $cookieValue; ?>"
  data-cookie-name-headless="<?php echo $cookieNameHeadless; ?>"
  data-requested-uri="<?php echo $requestedUri; ?>">

  <div id="challenge-container">
    <h2>
      <i class="fa fa-check-circle"></i>
      Verifying Your Humanity
    </h2>
    <p>
      Hang tight— we're just making sure you're a real person so our community stays safe.
      Redirecting in <span id="countdown"></span> seconds...
    </p>
    <noscript>
      <p>
        <strong>Notice:</strong> JavaScript is required for this step.
        Please enable JavaScript in your browser and try again.
      </p>
    </noscript>
  </div>
</section>
