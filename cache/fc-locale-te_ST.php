<?php return array (
  'common.changesSaved' => 'Ýóür çhåñgèþ håvè bèèñ þåvèð.',
  'common.language' => 'Låñgüågè',
  'common.languages' => 'Låñgüågèþ',
  'common.notApplicable' => 'Ñót Æpplîçåblè',
  'common.harvester2' => 'Hårvèþtèr2',
  'common.search' => 'ßèårçh',
  'common.delete' => 'Ðèlètè',
  'common.yes' => 'Ýèþ',
  'common.no' => 'Ñó',
  'common.on' => 'Òñ',
  'common.off' => 'Òff',
  'common.edit' => 'Éðît',
  'common.error.databaseError' => 'Æ ðåtåbåþè èrrór håþ óççürrèð: {$error}',
  'common.mailingAddress' => 'Måîlîñg Æððrèþþ',
  'common.save' => 'ßåvè',
  'common.cancel' => 'Çåñçèl',
  'common.requiredField' => '* Ðèñótèþ rèqüîrèð fîèlð',
  'common.action' => 'Æçtîóñ',
  'navigation.home' => 'Hómè',
  'navigation.help' => 'Hèlp',
  'navigation.about' => 'Æbóüt',
  'navigation.search' => 'ßèårçh',
  'navigation.login' => 'Lóg Îñ',
  'navigation.content' => 'Çóñtèñt',
  'navigation.loggedInAs' => 'Ýóü årè lóggèð îñ åþ...',
  'navigation.administration' => 'Æðmîñîþtråtîóñ',
  'navigation.logout' => 'Lóg Òüt',
  'navigation.previousPage' => '<<',
  'navigation.nextPage' => '>>',
  'navigation.items' => '{$from} - {$to} óf {$total} Îtèmþ',
  'installer.harvester2Installation' => 'PKP Hårvèþtèr2 Îñþtållåtîóñ',
  'installer.harvester2Upgrade' => 'Hårvèþtèr2 Üpgråðè',
  'installer.installationInstructions' => '<h4>PKP Hårvèþtèr2 Vèrþîóñ {$version}</h4>

<p>Thåñk ýóü fór ðówñlóåðîñg thè Püblîç Kñówlèðgè Prójèçt\'þ <strong>Hårvèþtèr2</strong>. Bèfórè próçèèðîñg, plèåþè rèåð thè <a href="{$baseUrl}/docs/README">RÉÆÐMÉ</a> fîlè îñçlüðèð wîth thîþ þóftwårè. Fór mórè îñfórmåtîóñ åbóüt thè Püblîç Kñówlèðgè Prójèçt åñð îtþ þóftwårè prójèçtþ, plèåþè vîþît thè <a href="http://pkp.sfu.ca/" target="_blank">PKP wèb þîtè</a>. Îf ýóü håvè büg rèpórtþ ór tèçhñîçål þüppórt îñqüîrîèþ åbóüt Hårvèþtèr2, þèè thè <a href="http://pkp.sfu.ca/support/forum">þüppórt fórüm</a> ór vîþît PKP\'þ óñlîñè <a href="http://pkp.sfu.ca/bugzilla/" target="_blank">büg rèpórtîñg þýþtèm</a>. Ælthóügh thè þüppórt fórüm îþ thè prèfèrrèð mèthóð óf çóñtåçt, ýóü çåñ ålþó èmåîl thè tèåm åt <a href="mailto:pkp-support@sfu.ca">pkp-þüppórt@þfü.çå</a>.</p>

<h4>Rèçómmèñðèð þýþtèm rèqüîrèmèñtþ</h4>

<ul>
	<li><a href="http://www.php.net/" target="_blank">PHP</a> >= 4.2.x (including PHP 5.x)</lî>
	<lî><å hrèf="http://www.mýþql.çóm/" tårgèt="_blåñk">MySQL</å> >= 3.23.23 (including MySQL 4.x) or <a href="http://www.postgresql.org/" target="_blank">PostgreSQL</a> >= 7.1 (including PostgreSQL 8.x)</li>
	<li><a href="http://httpd.apache.org/" target="_blank">Apache</a> >= 1.3.2x or >= 2.0.4x or Microsoft IIS 6</li>
	<li>Operating system: Any OS that supports the above software, including <a href="http://www.linux.org/" target="_blank">Linux</a>, <a href="http://www.bsd.org/" target="_blank">BSD</a>, <a href="http://www.sun.com/" target="_blank">Solaris</a>, <a href="http://www.apple.com/" target="_blank">Mac OS X</a>, <a href="http://www.microsoft.com/">Windows</a></li>
</ul>

<p>As PKP does not have the resources to test every possible combination of software versions and platforms, no guarantee of correct operation or support is implied.</p>

<p>Changes to these settings can be made after installation by editing the file <tt>config.inc.php</tt> in the base Harvester2 directory, or using the site administration web interface.</p>

<h4>Supported database systems</h4>

<p>Harvester2 has currently only been tested on MySQL and PostgreSQL, although other database management systems supported by <a href="http://php.weblogs.com/adodb/" target="_blank">ADOdb</a> may work (in full or partially). Compatibility reports and/or code patches for alternative DBMSs can be sent to the Harvester2 team.</p>

<h4>Pre-Installation Steps</h4>

<p>1. The following files and directories (and their contents) must be made writable:</p>
<ul>
	<li><tt>config.inc.php</tt> is writable (optional): {$writable_config}</li>
	<li><tt>cache/</tt> is writable: {$writable_cache}</li>
	<li><tt>cache/t_cache/</tt> is writable: {$writable_templates_cache}</li>
	<li><tt>cache/t_compile/</tt> is writable: {$writable_templates_compile}</li>
	<li><tt>cache/_db</tt> is writable: {$writable_db_cache}</li>
</ul>

<h4>Manual installation</h4>

<p>Select <strong>Manual Install</strong> to display the SQL statements necessary to create the Harvester2 database schema and initial data so that database installation can be performed manually. This option is useful when debugging installation problems, particularly on unsupported platforms. Note that this installation method will not attempt to create the database or any tables, although the configuration file will still be written with the supplied database settings.</p>',
  'installer.upgradeInstructions' => '<h4>Hårvèþtèr2 Vèrþîóñ {$version}</h4>
	
<p>Thåñk ýóü fór ðówñlóåðîñg thè Püblîç Kñówlèðgè Prójèçt\'þ <strong>Hårvèþtèr2</strong>. Bèfórè próçèèðîñg, plèåþè rèåð thè <a href="{$baseUrl}/docs/README">RÉÆÐMÉ</a> åñð <a href="{$baseUrl}/docs/UPGRADE">ÜPGRÆÐÉ</a> fîlèþ îñçlüðèð wîth thîþ þóftwårè. Fór mórè îñfórmåtîóñ åbóüt thè Püblîç Kñówlèðgè Prójèçt åñð îtþ þóftwårè prójèçtþ, plèåþè vîþît thè <a href="http://pkp.sfu.ca/" target="_blank">PKP wèb þîtè</a>. Îf ýóü håvè büg rèpórtþ ór tèçhñîçål þüppórt îñqüîrîèþ åbóüt Hårvèþtèr2, þèè thè <a href="http://pkp.sfu.ca/support/forum">þüppórt fórüm</a> ór vîþît PKP\'þ óñlîñè <a href="http://pkp.sfu.ca/bugzilla/" target="_blank">büg rèpórtîñg þýþtèm</a>. Ælthóügh thè þüppórt fórüm îþ thè prèfèrrèð mèthóð óf çóñtåçt, ýóü çåñ ålþó èmåîl thè tèåm åt <a href="mailto:pkp-support@sfu.ca">pkp-þüppórt@þfü.çå</a>.</p>
<p>Ît îþ <strong>þtróñglý rèçómmèñðèð</strong> thåt ýóü båçk üp ýóür ðåtåbåþè, fîlèþ ðîrèçtórý, åñð Hårvèþtèr2 îñþtållåtîóñ ðîrèçtórý bèfórè próçèèðîñg.</p>
<p>Îf ýóü årè rüññîñg îñ <a href="http://www.php.net/features.safe-mode" target="_blank">PHP ßåfè Móðè</a>, plèåþè èñþürè thåt thè måx_èxèçütîóñ_tîmè ðîrèçtîvè îñ ýóür php.îñî çóñfîgüråtîóñ fîlè îþ þèt tó å hîgh lîmît. Îf thîþ ór åñý óthèr tîmè lîmît (è.g. Æpåçhè\'þ "Tîmèóüt" ðîrèçtîvè) îþ rèåçhèð åñð thè üpgråðè próçèþþ îþ îñtèrrüptèð, måñüål îñtèrvèñtîóñ wîll bè rèqüîrèð.</p>',
  'installer.localeSettings' => 'Lóçålè ßèttîñgþ',
  'installer.localeSettingsInstructions' => 'Fór çómplètè Üñîçóðè (ÜTF-8) þüppórt, þèlèçt ÜTF-8 fór åll çhåråçtèr þèt þèttîñgþ. Ñótè thåt thîþ þüppórt çürrèñtlý rèqüîrèþ å MýßQL >= 4.1.1 or PostgreSQL >= 7.1 database server. Please also note that full Unicode support requires PHP >= 4.3.0 compiled with support for the <a href="http://www.php.net/mbstring" target="_blank">mbstring</a> library (enabled by default in most recent PHP installations). You may experience problems using extended character sets if your server does not meet these requirements.
<br /><br />
Your server currently supports mbstring: <strong>{$supportsMBString}</strong>',
  'installer.localeInstructions' => 'Thè prîmårý låñgüågè tó üþè fór thîþ þýþtèm. Plèåþè çóñþült thè Hårvèþtèr2 ðóçümèñtåtîóñ îf ýóü årè îñtèrèþtèð îñ þüppórt fór låñgüågèþ ñót lîþtèð hèrè.',
  'installer.additionalLocales' => 'Æððîtîóñål lóçålèþ',
  'installer.additionalLocalesInstructions' => 'ßèlèçt åñý åððîtîóñål låñgüågèþ tó þüppórt îñ thîþ þýþtèm. Æððîtîóñål låñgüågèþ çåñ ålþó bè îñþtållèð åt åñý tîmè fróm thè þîtè åðmîñîþtråtîóñ îñtèrfåçè.',
  'installer.clientCharset' => 'Çlîèñt çhåråçtèr þèt',
  'installer.clientCharsetInstructions' => 'Thè èñçóðîñg tó üþè fór ðåtå þèñt tó åñð rèçèîvèð fróm brówþèrþ.',
  'installer.connectionCharset' => 'Çóññèçtîóñ çhåråçtèr þèt',
  'installer.connectionCharsetInstructions' => 'Thè èñçóðîñg tó üþè fór ðåtå þèñt tó åñð rèçèîvèð fróm thè ðåtåbåþè. Thîþ þhóülð bè thè þåmè åþ thè çlîèñt çhåråçtèr þèt. Ñótè thåt thîþ çåpåbîlîtý îþ óñlý þüppórtèð wîth MýßQL >= 4.1.1 or PostgreSQL >= 7.1. Select "Not available" if your database server does not meet these requirements.',
  'installer.databaseCharset' => 'Ðåtåbåþè çhåråçtèr þèt',
  'installer.databaseCharsetInstructions' => 'Thè èñçóðîñg tó üþè fór ðåtå þtórèð îñ thè ðåtåbåþè. Ñótè thåt thîþ çåpåbîlîtý îþ óñlý þüppórtèð wîth MýßQL >= 4.1.1 or PostgreSQL >= 7.1. Select "Not available" if your database server does not meet these requirements.',
  'installer.locale' => 'Lóçålè',
  'installer.securitySettings' => 'ßèçürîtý ßèttîñgþ',
  'installer.encryption' => 'Påþþwórð èñçrýptîóñ ålgórîthm',
  'installer.encryptionInstructions' => 'ßHÆ1 îþ rèçómmèñðèð îf ýóür þýþtèm þüppórtþ ît (rèqüîrèþ PHP >= 4.3.0).',
  'installer.administratorAccount' => 'Æðmîñîþtråtór Æççóüñt',
  'installer.administratorAccountInstructions' => 'Thîþ üþèr åççóüñt wîll bèçómè thè þîtè åðmîñîþtråtór åñð håvè çómplètè åççèþþ tó thè þýþtèm. Æððîtîóñål üþèr åççóüñtþ çåñ bè çrèåtèð åftèr îñþtållåtîóñ.',
  'installer.databaseSettings' => 'Ðåtåbåþè ßèttîñgþ',
  'installer.databaseDriver' => 'Ðåtåbåþè ðrîvèr',
  'installer.databaseHost' => 'Hóþt',
  'installer.databaseUsername' => 'Üþèrñåmè',
  'installer.databasePassword' => 'Påþþwórð',
  'installer.databaseName' => 'Ðåtåbåþè ñåmè',
  'installer.databaseDriverInstructions' => '<strong>Ðåtåbåþè ðrîvèrþ lîþtèð îñ bråçkètþ ðó ñót åppèår tó håvè thè rèqüîrèð PHP èxtèñþîóñ lóåðèð åñð îñþtållåtîóñ wîll lîkèlý fåîl îf þèlèçtèð.</strong><br />Æñý üñþüppórtèð ðåtåbåþè ðrîvèrþ lîþtèð åbóvè årè lîþtèð þólèlý fór åçåðèmîç pürpóþèþ åñð årè üñlîkèlý tó wórk.',
  'installer.databaseHostInstructions' => 'Lèåvè thè hóþtñåmè blåñk tó çóññèçt üþîñg ðómåîñ þóçkètþ îñþtèåð óf óvèr TÇP/ÎP. Thîþ îþ ñót ñèçèþþårý wîth MýßQL, whîçh wîll åütómåtîçållý üþè þóçkètþ îf "lóçålhóþt" îþ èñtèrèð, büt îþ rèqüîrèð wîth þómè óthèr ðåtåbåþè þèrvèrþ þüçh åþ PóþtgrèßQL.',
  'installer.createDatabase' => 'Çrèåtè ñèw ðåtåbåþè',
  'installer.createDatabaseInstructions' => 'Tó üþè thîþ óptîóñ ýóür ðåtåbåþè þýþtèm müþt þüppórt rèmótè ðåtåbåþè çrèåtîóñ åñð ýóür üþèr åççóüñt müþt håvè thè åppróprîåtè pèrmîþþîóñþ tó çrèåtè ñèw ðåtåbåþèþ. Îf îñþtållåtîóñ fåîlþ wîth thîþ óptîóñ þèlèçtèð, måñüållý çrèåtè thè ðåtåbåþè óñ ýóür þèrvèr åñð rüñ thè îñþtållèr ågåîñ wîth thîþ óptîóñ ðîþåblèð.',
  'installer.installHarvester2' => 'Îñþtåll Hårvèþtèr2',
  'installer.upgradeHarvester2' => 'Üpgråðè Hårvèþtèr2',
  'installer.manualInstall' => 'Måñüål Îñþtåll',
  'installer.manualUpgrade' => 'Måñüål Üpgråðè',
  'installer.form.localeRequired' => 'Æ lóçålè müþt bè þèlèçtèð.',
  'installer.form.clientCharsetRequired' => 'Æ çlîèñt çhåråçtèr þèt müþt bè þèlèçtèð.',
  'installer.form.filesDirRequired' => 'Thè ðîrèçtórý tó bè üþèð fór þtórîñg üplóåðèð fîlèþ îþ rèqüîrèð.',
  'installer.form.encryptionRequired' => 'Thè ålgórîthm tó üþè fór èñçrýptîñg üþèr påþþwórðþ müþt bè þèlèçtèð.',
  'installer.form.databaseDriverRequired' => 'Æ ðåtåbåþè ðrîvèr müþt bè þèlèçtèð.',
  'installer.form.databaseNameRequired' => 'Thè ðåtåbåþè ñåmè îþ rèqüîrèð.',
  'installer.form.usernameRequired' => 'Æ üþèrñåmè fór thè åðmîñîþtråtór åççóüñt îþ rèqüîrèð.',
  'installer.form.passwordRequired' => 'Æ påþþwórð fór thè åðmîñîþtråtór åççóüñt îþ rèqüîrèð.',
  'installer.form.usernameAlphaNumeric' => 'Thè åðmîñîþtråtór üþèrñåmè çåñ çóñtåîñ óñlý ålphåñümèrîç çhåråçtèrþ, üñðèrþçórèþ, åñð hýphèñþ, åñð müþt bègîñ åñð èñð wîth åñ ålphåñümèrîç çhåråçtèr.',
  'installer.form.passwordsDoNotMatch' => 'Thè åðmîñîþtråtór påþþwórðþ ðó ñót måtçh.',
  'installer.form.emailRequired' => 'Æ vålîð èmåîl åððrèþþ fór thè åðmîñîþtråtór åççóüñt îþ rèqüîrèð.',
  'installer.form.separateMultiple' => 'ßèpåråtè mültîplè vålüèþ wîth çómmåþ',
  'installer.installErrorsOccurred' => 'Érrórþ óççürrèð ðürîñg îñþtållåtîóñ',
  'installer.installFilesDirError' => 'Thè ðîrèçtórý þpèçîfîèð fór üplóåðèð fîlèþ ðóèþ ñót èxîþt ór îþ ñót wrîtåblè.',
  'installer.publicFilesDirError' => 'Thè püblîç fîlèþ ðîrèçtórý ðóèþ ñót èxîþt ór îþ ñót wrîtåblè.',
  'installer.installFileError' => 'Thè îñþtållåtîóñ fîlè <tt>ðbþçrîptþ/xml/îñþtåll.xml</tt> ðóèþ ñót èxîþt ór îþ ñót rèåðåblè.',
  'installer.installParseDBFileError' => 'Érrór pårþîñg thè ðåtåbåþè îñþtållåtîóñ fîlè <tt>{$file}</tt>.',
  'installer.configFileError' => 'Thè çóñfîgüråtîóñ fîlè <tt>çóñfîg.îñç.php</tt> ðóèþ ñót èxîþt ór îþ ñót rèåðåblè.',
  'installer.reinstallAfterDatabaseError' => '<b>Wårñîñg:</b> Îf îñþtållåtîóñ fåîlèð pårt wåý thróügh ðåtåbåþè îñþtållåtîóñ ýóü måý ñèèð tó ðróp ýóür Hårvèþtèr2 ðåtåbåþè ór ðåtåbåþè tåblèþ bèfórè åttèmptîñg tó rèîñþtåll thè ðåtåbåþè.',
  'installer.overwriteConfigFileInstructions' => '<h4>ÎMPÒRTÆÑT!</h4>
<p>Thè îñþtållèr çóülð ñót åütómåtîçållý óvèrwrîtè thè çóñfîgüråtîóñ fîlè. Bèfórè åttèmptîñg tó üþè thè þýþtèm, plèåþè ópèñ <tt>çóñfîg.îñç.php</tt> îñ å þüîtåblè tèxt èðîtór åñð rèplåçè îtþ çóñtèñtþ wîth thè çóñtèñtþ óf thè tèxt fîèlð bèlów.</p>',
  'installer.contentsOfConfigFile' => 'Çóñtèñtþ óf çóñfîgüråtîóñ fîlè',
  'installer.manualSQLInstructions' => '<h4>Måñüål îñþtållåtîóñ</h4>
<p>Thè ßQL þtåtèmèñtþ tó çrèåtè thè Hårvèþtèr2 ðåtåbåþè þçhèmå åñð îñîtîål ðåtå årè ðîþplåýèð bèlów. Ñótè thåt thè þýþtèm wîll bè üñüþåblè üñtîl thèþè þtåtèmèñtþ håvè bèèñ èxèçütèð måñüållý. Ýóü wîll ålþó håvè tó måñüållý çóñfîgürè thè <tt>çóñfîg.îñç.php</tt> çóñfîgüråtîóñ fîlè.</p>',
  'installer.installerSQLStatements' => 'ßQL þtåtèmèñtþ fór îñþtållåtîóñ',
  'installer.installationComplete' => '<p>Îñþtållåtîóñ óf Hårvèþtèr2 håþ çómplètèð þüççèþþfüllý.</p>
<p>Tó bègîñ üþîñg thè þýþtèm, <a href="{$indexUrl}/login">lógîñ</a> wîth thè üþèrñåmè åñð påþþwórð èñtèrèð óñ thè prèvîóüþ pågè.</p>',
  'installer.upgradeComplete' => '<p>Üpgråðè óf Hårvèþtèr2 tó vèrþîóñ {$version} håþ çómplètèð þüççèþþfüllý.</p>
<p>Ðóñ\'t fórgèt tó þèt thè "îñþtållèð" þèttîñg îñ ýóür çóñfîg.îñç.php çóñfîgüråtîóñ fîlè båçk tó <i>Òñ</i>.</p>',
  'installer.releaseNotes' => 'Rèlèåþè Ñótèþ',
  'installer.checkYes' => 'Ýèþ',
  'installer.checkNo' => '<span class="formError">ÑÒ</span>',
  'locale.primary' => 'Prîmårý Lóçålè',
  'locale.supported' => 'ßüppórtèð Lóçålèþ',
  'user.login' => 'Lóg Îñ',
  'user.logout' => 'Lóg Òüt',
  'user.username' => 'Üþèrñåmè',
  'user.password' => 'Påþþwórð',
  'user.email' => 'Émåîl',
  'user.register.repeatPassword' => 'Rèpèåt Påþþwórð',
  'user.login.loginError' => 'Îñvålîð üþèrñåmè ór påþþwórð. Plèåþè trý ågåîñ.',
  'user.login.rememberMe' => 'Rèmèmbèr mè',
  'user.login.rememberUsernameAndPassword' => 'Rèmèmbèr mý üþèrñåmè åñð påþþwórð',
  'form.errorsOccurred' => 'Érrórþ óççürrèð próçèþþîñg thîþ fórm',
  'archive.title' => 'Tîtlè',
  'archive.url' => 'ÜRL',
  'archive.oaiUrl' => 'ÒÆÎ Båþè ÜRL',
  'archive.description' => 'Ðèþçrîptîóñ',
  'admin.siteAdmin' => 'ßîtè Æðmîñîþtråtîóñ',
  'admin.siteSettings' => 'ßîtè ßèttîñgþ',
  'admin.archives' => 'Ærçhîvèþ',
  'admin.settings.siteTitle' => 'ßîtè tîtlè',
  'admin.settings.introduction' => 'Îñtróðüçtîóñ',
  'admin.settings.aboutDescription' => 'Æbóüt thè ßîtè ðèþçrîptîóñ',
  'admin.settings.form.titleRequired' => 'Æ tîtlè îþ rèqüîrèð.',
  'admin.settings.contactName' => 'Ñåmè óf prîñçîpål çóñtåçt',
  'admin.settings.contactEmail' => 'Émåîl óf prîñçîpål çóñtåçt',
  'admin.settings.form.contactNameRequired' => 'Thè ñåmè óf thè prîñçîpål çóñtåçt îþ rèqüîrèð.',
  'admin.settings.form.contactEmailRequired' => 'Thè èmåîl åððrèþþ óf thè prîñçîpål çóñtåçt îþ rèqüîrèð.',
  'admin.settings.siteLanguage' => 'ßîtè låñgüågè',
  'admin.siteManagement' => 'ßîtè Måñågèmèñt',
  'admin.adminFunctions' => 'Æðmîñîþtråtîvè Füñçtîóñþ',
  'admin.expireSessions' => 'Éxpîrè Üþèr ßèþþîóñþ',
  'admin.confirmExpireSessions' => 'Ærè ýóü þürè ýóü wåñt tó èxpîrè åll üþèr þèþþîóñþ? Ýóü wîll bè fórçèð tó lóg îñ ågåîñ.',
  'admin.clearTemplateCache' => 'Çlèår Tèmplåtè Çåçhè',
  'admin.clearDataCache' => 'Çlèår Ðåtå Çåçhèþ',
  'admin.confirmClearTemplateCache' => 'Ærè ýóü þürè ýóü wåñt tó çlèår thè çåçhè óf çómpîlèð tèmplåtèþ?',
  'admin.systemInformation' => 'ßýþtèm Îñfórmåtîóñ',
  'admin.archives.addArchive' => 'Æðð Ærçhîvè',
  'admin.archives.editArchive' => 'Éðît Ærçhîvè',
  'admin.archives.manageArchives' => 'Måñågè Ærçhîvèþ',
  'admin.archives.noneCreated' => 'Ñóñè Çrèåtèð',
  'admin.archives.form.titleRequired' => 'Æ tîtlè îþ rèqüîrèð.',
  'admin.archives.form.urlRequired' => 'Thè ÜRL fîèlð îþ rèqüîrèð.',
  'admin.archives.form.oaiUrlRequired' => 'Thè ÒÆÎ ÜRL fîèlð îþ rèqüîrèð.',
  'admin.archives.form.url.description' => 'è.g. http://www.ýóürårçhîvè.çóm',
  'admin.archives.form.oaiUrl.description' => 'è.g. http://www.ýóürårçhîvè.çóm/óåî/îñðèx.php',
  'admin.languages.languageSettings' => 'Låñgüågè ßèttîñgþ',
  'admin.languages.installLanguages' => 'Îñþtåll Låñgüågèþ',
  'admin.languages.primaryLocaleInstructions' => 'Thîþ wîll bè thè ðèfåült låñgüågè fór thè þîtè.',
  'admin.languages.supportedLocalesInstructions' => 'ßèlèçt åll lóçålèþ tó þüppórt óñ thè þîtè. Îf mültîplè lóçålèþ årè ñót þèlèçtèð, thè låñgüågè tógglè mèñü wîll ñót åppèår åñð èxtèñðèð låñgüågè þèttîñgþ wîll ñót bè åvåîlåblè.',
  'admin.languages.languageOptions' => 'Låñgüågè óptîóñþ',
  'admin.languages.installedLocales' => 'Îñþtållèð Lóçålèþ',
  'admin.languages.reload' => 'Rèlóåð Lóçålè',
  'admin.languages.confirmReload' => 'Ærè ýóü þürè ýóü wåñt tó rèlóåð thîþ lóçålè? Thîþ wîll èråþè åñý èxîþtîñg lóçålè-þpèçîfîç ðåtå þüçh åþ çüþtómîzèð èmåîl tèmplåtèþ.',
  'admin.languages.uninstall' => 'Üñîñþtåll Lóçålè',
  'admin.languages.confirmUninstall' => 'Ærè ýóü þürè ýóü wåñt tó üñîñþtåll thîþ lóçålè?',
  'admin.languages.installNewLocales' => 'Îñþtåll Ñèw Lóçålèþ',
  'admin.languages.installNewLocalesInstructions' => 'ßèlèçt åñý åððîtîóñål lóçålèþ tó îñþtåll þüppórt fór îñ thîþ þýþtèm. ßèè thè Hårvèþtèr2 ðóçümèñtåtîóñ fór îñfórmåtîóñ óñ åððîñg þüppórt fór ñèw låñgüågèþ.',
  'admin.languages.noLocalesAvailable' => 'Ñó åððîtîóñål lóçålèþ årè åvåîlåblè fór îñþtållåtîóñ.',
  'admin.languages.installLocales' => 'Îñþtåll',
  'admin.systemVersion' => 'Hårvèþtèr2 Vèrþîóñ',
  'admin.systemConfiguration' => 'Hårvèþtèr2 Çóñfîgüråtîóñ',
  'admin.serverInformation' => 'ßèrvèr Îñfórmåtîóñ',
  'admin.currentVersion' => 'Çürrèñt vèrþîóñ',
  'admin.versionHistory' => 'Vèrþîóñ hîþtórý',
  'admin.version' => 'Vèrþîóñ',
  'admin.versionMajor' => 'Måjór',
  'admin.versionMinor' => 'Mîñór',
  'admin.versionRevision' => 'Rèvîþîóñ',
  'admin.versionBuild' => 'Büîlð',
  'admin.dateInstalled' => 'Ðåtè îñþtållèð',
  'admin.systemConfigurationDescription' => 'Hårvèþtèr2 çóñfîgüråtîóñ þèttîñgþ fróm <tt>çóñfîg.îñç.php</tt>.',
  'admin.serverInformationDescription' => 'Båþîç ópèråtîñg þýþtèm åñð þèrvèr þóftwårè vèrþîóñþ. Çlîçk óñ <span class="highlight">Éxtèñðèð PHP Îñfórmåtîóñ</span> tó vîèw èxtèñðèð ðètåîlþ óf thîþ þèrvèr\'þ PHP çóñfîgüråtîóñ.',
  'admin.phpInfo' => 'Éxtèñðèð PHP Îñfórmåtîóñ',
  'admin.server.platform' => 'Òß plåtfórm',
  'admin.server.phpVersion' => 'PHP vèrþîóñ',
  'admin.server.apacheVersion' => 'Æpåçhè vèrþîóñ',
  'admin.server.dbDriver' => 'Ðåtåbåþè ðrîvèr',
  'admin.server.dbVersion' => 'Ðåtåbåþè þèrvèr vèrþîóñ',
  'admin.editSystemConfigInstructions' => 'Üþè thîþ fórm tó móðîfý ýóür þýþtèm çóñfîgüråtîóñ (thè <tt>çóñfîg.îñç.php</tt> fîlè). Çlîçk ßåvè tó þåvè ýóür ñèw çóñfîgüråtîóñ, ór "Ðîþplåý" tó þîmplý ðîþplåý thè üpðåtèð çóñfîgüråtîóñ fîlè åñð ñót móðîfý ýóür èxîþtîñg çóñfîgüråtîóñ.
<br /><br />
<span class="formError">Wårñîñg: Móðîfýîñg thèþè þèttîñgþ çåñ pótèñtîållý lèåvè ýóür þîtè îñ åñ îñåççèþþîblè þtåtè (rèqüîrîñg ýóür çóñfîgüråtîóñ fîlè tó bè måñüållý fîxèð). Ît îþ þtróñglý rèçómmèñðèð thåt ýóü ðó ñót måkè åñý çhåñgèþ üñlèþþ ýóü kñów èxåçtlý whåt ýóü årè ðóîñg.</span>',
  'admin.saveSystemConfig' => 'ßåvè Çóñfîgüråtîóñ',
  'admin.displayNewSystemConfig' => 'Ðîþplåý Ñèw Çóñfîgüråtîóñ',
  'admin.systemConfigFileReadError' => 'Thè çóñfîgüråtîóñ fîlè <tt>çóñfîg.îñç.php</tt> ðóèþ ñót èxîþt, îþ ñót rèåðåblè, ór îþ îñvålîð.',
  'admin.overwriteConfigFileInstructions' => '<h4>ÑÒTÉ!</div>
<p>Thè þýþtèm çóülð ñót åütómåtîçållý óvèrwrîtè thè çóñfîgüråtîóñ fîlè. Tó åpplý ýóür çóñfîgüråtîóñ çhåñgèþ ýóü müþt ópèñ <tt>çóñfîg.îñç.php</tt> îñ å þüîtåblè tèxt èðîtór åñð rèplåçè îtþ çóñtèñtþ wîth thè çóñtèñtþ óf thè tèxt fîèlð bèlów.</p>',
  'admin.displayConfigFileInstructions' => 'Thè çóñtèñtþ óf ýóür üpðåtèð çóñfîgüråtîóñ årè ðîþplåýèð bèlów. Tó åpplý thè çóñfîgüråtîóñ çhåñgèþ ýóü müþt ópèñ <tt>çóñfîg.îñç.php</tt> îñ å þüîtåblè tèxt èðîtór åñð rèplåçè îtþ çóñtèñtþ wîth thè çóñtèñtþ óf thè tèxt fîèlð bèlów.',
  'admin.configFileUpdatedInstructions' => 'Ýóür çóñfîgüråtîóñ fîlè håþ bèèñ þüççèþþfüllý üpðåtèð. Plèåþè ñótè thåt îf ýóür þîtè ñó lóñgèr füñçtîóñþ çórrèçtlý ýóü måý ñèèð tó måñüållý fîx ýóür çóñfîgüråtîóñ bý èðîtîñg <tt>çóñfîg.îñç.php</tt> ðîrèçtlý.',
  'admin.contentsOfConfigFile' => 'Çóñtèñtþ óf çóñfîgüråtîóñ fîlè',
  'admin.version.checkForUpdates' => 'Çhèçk fór üpðåtèþ',
  'admin.version.latest' => 'Låtèþt vèrþîóñ',
  'admin.version.upToDate' => 'Ýóür þýþtèm îþ üp-tó-ðåtè',
  'admin.version.updateAvailable' => 'Æñ üpðåtèð vèrþîóñ îþ åvåîlåblè',
  'admin.version.downloadPackage' => 'Ðówñlóåð',
  'admin.version.downloadPatch' => 'Ðówñlóåð Påtçh',
  'admin.version.moreInfo' => 'Mórè Îñfórmåtîóñ',
  'sidebar.harvesterStats' => 'Hårvèþtèr ßtåtþ',
  'sidebar.harvesterStats.description' => 'Hårvèþtèr2 çürrèñtlý håþ <strong>FÎXMÉ</strong> påpèrþ fróm <strong>FÎXMÉ</strong> årçhîvè(þ) îñðèxèð.',
  'sidebar.addYourArchive' => 'Æðð Ýóür Ærçhîvè',
  'sidebar.addYourArchive.description' => '<a href="{$addUrl}">Çlîçk hèrè</a> tó åðð ýóür þýþtèm tó óür îñðèx.',
  'default.siteIntro' => '<strong>Wèlçómè tó thè Püblîç Kñówlèðgè Prójèçt\'þ mètåðåtå årçhîvè...</strong>
	
	Tó împróvè thè åççüråçý óf þèårçhîñg wîthîñ thè PKP ßýþtèm, åüthórþ håvè bèèñ åþkèð tó îñðèx thèîr wórk, whèrè åpplîçåblè, bý ðîþçîplîñè(þ), tópîçþ, gèñrè, mèthóð, çóvèrågè, åñð þåmplè. Thîþ ållówþ ýóü tó þèårçh fór "èmpîrîçål" vèrþüþ "hîþtórîçål" þtüðîèþ, fór èxåmplè, üñðèr "îñðèx tèrmþ." Ýóü çåñ ålþó vîèw å ðóçümèñt\'þ îñðèx tèrmþ bý þèlèçtîñg thè çómplètè rèçórð fróm åmóñg thè þèårçh rèþültþ.',
  'default.emailSignature' => '________________________________________________________________________
	Hårvèþtèr2
	{$indexUrl}',
  'default.footer' => '&amp;çópý; 2005 <a href="http://pkp.sfu.ca/harvester2">Püblîç Kñówlèðgè Prójèçt</a>',
  'about.harvester' => 'Æbóüt thè Hårvèþtèr',
  'about.harvester.description' => 'Thè PKP Òpèñ Ærçhîvèþ Hårvèþtèr îþ å frèè mètåðåtå îñðèxîñg þýþtèm ðèvèlópèð bý thè Püblîç Kñówlèðgè Prójèçt thróügh îtþ fèðèrållý füñðèð èffórtþ tó èxpåñð åñð împróvè åççèþþ tó rèþèårçh. Thè PKP ÒÆÎ Hårvèþtèr ållówþ ýóü tó çrèåtè å þèårçhåblè îñðèx óf thè mètåðåtå fróm Òpèñ Ærçhîvèþ Îñîtîåtîvè-çómplîåñt årçhîvèþ, þüçh åþ þîtèþ üþîñg Òpèñ Jóürñål ßýþtèmþ ór Òpèñ Çóñfèrèñçè ßýþtèmþ.
	
	Thè PKP ÒÆÎ Hårvèþtèr îþ çürrèñtlý çómpåtîblè wîth vèrþîóñþ 1.1 åñð 2.0 óf thè ÒÆÎ Hårvèþtîñg Prótóçól.',
); ?>