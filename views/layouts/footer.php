</div><!-- /.app-content -->

<footer class="app-footer">
  <span>VoteSecure <?= APP_VERSION ?> &nbsp;|&nbsp; AI-Integrated Election System &nbsp;|&nbsp; Capstone Project</span>
  <span><?= date('Y') ?></span>
</footer>

<script src="<?= APP_URL ?>/public/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
<script><?= $extraJs ?></script>
<?php endif; ?>
</body>
</html>