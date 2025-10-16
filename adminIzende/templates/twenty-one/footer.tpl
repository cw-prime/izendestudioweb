                    </div>

                    </div>
                    {if !$inShoppingCart && $secondarySidebar->hasChildren()}
                        <div class="d-lg-none sidebar sidebar-secondary">
                            {include file="$template/includes/sidebar.tpl" sidebar=$secondarySidebar}
                        </div>
                    {/if}
                <div class="clearfix"></div>
            </div>
        </div>
    </section>

    <footer id="footer" class="footer">
        <div class="container">
            <ul class="list-inline mb-7 text-center float-lg-right">
                {include file="$template/includes/social-accounts.tpl"}

                {if $languagechangeenabled && count($locales) > 1 || $currencies}
                    <li class="list-inline-item">
                        <button type="button" class="btn" data-toggle="modal" data-target="#modalChooseLanguage">
                            <div class="d-inline-block align-middle">
                                <div class="iti-flag {if $activeLocale.countryCode === '001'}us{else}{$activeLocale.countryCode|lower}{/if}"></div>
                            </div>
                            {$activeLocale.localisedName}
                            /
                            {$activeCurrency.prefix}
                            {$activeCurrency.code}
                        </button>
                    </li>
                {/if}
            </ul>

            <ul class="nav justify-content-center justify-content-lg-start mb-7">
                <li class="nav-item">
                    <a class="nav-link" href="{$WEB_ROOT}/contact.php">
                        {lang key='contactus'}
                    </a>
                </li>
                {if $acceptTOS}
                    <li class="nav-item">
                        <a class="nav-link" href="{$tosURL}" target="_blank">{lang key='ordertos'}</a>
                    </li>
                {/if}
            </ul>

            <div class="footer-service-area text-center text-lg-left mb-4" style="color: rgba(255,255,255,0.8);">
                <p class="mb-1"><i class="fas fa-map-marker-alt"></i> <strong>Serving St. Louis Metro, Missouri & Illinois</strong></p>
                <p class="mb-0" style="font-size: 13px;">Professional web design, hosting, and digital marketing services for businesses throughout the St. Louis region and beyond.</p>
            </div>

            <div class="footer-legal-links" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <ul class="nav justify-content-center text-center" style="font-size: 13px;">
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/privacy-policy.php">Privacy Policy</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/terms-of-service.php">Terms of Service</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/cookie-policy.php">Cookie Policy</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/refund-policy.php">Refund Policy</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/service-level-agreement.php">SLA</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/accessibility-statement.php">Accessibility</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://izendestudioweb.com/do-not-sell.php">Do Not Sell or Share</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" id="cookie-settings-link">Cookie Settings</a></li>
                </ul>
            </div>

            <p class="copyright mb-0 text-center text-lg-left">
                {lang key="copyrightFooterNotice" year=$date_year company=$companyname}
            </p>
        </div>
    </footer>

    <div id="fullpage-overlay" class="w-hidden">
        <div class="outer-wrapper">
            <div class="inner-wrapper">
                <img src="{$WEB_ROOT}/assets/img/overlay-spinner.svg" alt="">
                <br>
                <span class="msg"></span>
            </div>
        </div>
    </div>

    <div class="modal system-modal fade" id="modalAjax" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{lang key='close'}</span>
                    </button>
                </div>
                <div class="modal-body">
                    {lang key='loading'}
                </div>
                <div class="modal-footer">
                    <div class="float-left loader">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        {lang key='loading'}
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {lang key='close'}
                    </button>
                    <button type="button" class="btn btn-primary modal-submit">
                        {lang key='submit'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form method="get" action="{$currentpagelinkback}">
        <div class="modal modal-localisation" id="modalChooseLanguage" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                        {if $languagechangeenabled && count($locales) > 1}
                            <h5 class="h5 pt-5 pb-3">{lang key='chooselanguage'}</h5>
                            <div class="row item-selector">
                                <input type="hidden" name="language" data-current="{$language}" value="{$language}" />
                                {foreach $locales as $locale}
                                    <div class="col-4">
                                        <a href="#" class="item{if $language == $locale.language} active{/if}" data-value="{$locale.language}">
                                            {$locale.localisedName}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                        {if !$loggedin && $currencies}
                            <p class="h5 pt-5 pb-3">{lang key='choosecurrency'}</p>
                            <div class="row item-selector">
                                <input type="hidden" name="currency" data-current="{$activeCurrency.id}" value="">
                                {foreach $currencies as $selectCurrency}
                                    <div class="col-4">
                                        <a href="#" class="item{if $activeCurrency.id == $selectCurrency.id} active{/if}" data-value="{$selectCurrency.id}">
                                            {$selectCurrency.prefix} {$selectCurrency.code}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-default">{lang key='apply'}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {if !$loggedin && $adminLoggedIn}
        <a href="{$WEB_ROOT}/logout.php?returntoadmin=1" class="btn btn-return-to-admin" data-toggle="tooltip" data-placement="bottom" title="{if $adminMasqueradingAsClient}{lang key='adminmasqueradingasclient'} {lang key='logoutandreturntoadminarea'}{else}{lang key='adminloggedin'} {lang key='returntoadminarea'}{/if}">
            <i class="fas fa-redo-alt"></i>
            <span class="d-none d-md-inline-block">{lang key="admin.returnToAdmin"}</span>
        </a>
    {/if}

    {include file="$template/includes/generate-password.tpl"}

    {$footeroutput}

</body>
</html>
