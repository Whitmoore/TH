{* Don't delete it *}
<script type="application/ld+json">
{$ldelim}
    "@context": "http://schema.org",
    "@type": "Organization",
    "url": "{$config.current_url|fn_url}",
    "address": {$ldelim}
        "@type": "PostalAddress",
        "addressLocality": "{$settings.Company.company_city}, {$settings.Company.company_country|fn_get_country_name}"
    {$rdelim},
    "email": "{$settings.Company.company_users_department}",
    "name": "{$settings.Company.company_name}",
    "telephone": "{$settings.Company.company_phone}",
    "logo": "http://www.tennishouse.ru/images/companies/1/logo_white.png"
{$rdelim}
</script>