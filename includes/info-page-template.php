<?php
require_once __DIR__ . '/header.php';
?>

<section class="info-page" aria-labelledby="infoPageTitle">
    <div class="info-page-inner">
        <div class="info-hero">
            <p class="eyebrow"><?php echo htmlspecialchars($info_kicker ?? 'Shenanovents', ENT_QUOTES, 'UTF-8'); ?></p>
            <h1 id="infoPageTitle"><?php echo htmlspecialchars($info_title ?? 'Information', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($info_intro ?? 'Helpful information for using Shenanovents.', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <?php if (!empty($info_cards)): ?>
            <div class="info-card-grid">
                <?php foreach ($info_cards as $card): ?>
                    <article class="info-card">
                        <h2><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($card['body'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($info_sections)): ?>
            <div class="info-section-list">
                <?php foreach ($info_sections as $section): ?>
                    <article class="info-detail-card">
                        <h2><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <?php if (!empty($section['body'])): ?>
                            <p><?php echo htmlspecialchars($section['body'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($section['items'])): ?>
                            <ul>
                                <?php foreach ($section['items'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($info_cta)): ?>
            <div class="info-cta">
                <div>
                    <h2><?php echo htmlspecialchars($info_cta['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($info_cta['body'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a class="button button-primary" href="<?php echo htmlspecialchars($info_cta['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($info_cta['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
