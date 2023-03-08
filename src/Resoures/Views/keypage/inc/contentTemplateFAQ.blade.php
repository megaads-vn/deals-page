<div class="aside-box faq-template">
    <h2 class="aside-title box-coupon-title">
        FAQ for {{ $keyword['keyword'] }} {{ date('M Y') }}
    </h2>
    <div class="mb-content" id="faq-content-template">
        <?= $contentTemplateFAQ ?>
        <div class="faq-title feedback-link">
            <a rel="nofollow" href="<?= route('frontend::home::feedback') ?>">Feedback</a>
        </div>
    </div>
    
</div>

<script>
    let faqElements = document.querySelectorAll('#faq-content-template .faq-title');
    for (let item of faqElements) {
        item.addEventListener('click', function () {
            item.classList.toggle('active');
        })
    }
</script>
