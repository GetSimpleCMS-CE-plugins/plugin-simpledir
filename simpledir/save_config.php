<style>
input.submit{
	border-radius:5px !important;
	background:#4CAF50 !important;
	color:white !important;
	text-shadow:none;
	padding:10px 15px
}
</style>
<div class="w3-parent w3-container">
	<h3 xclass="floated"><?php simpledir_i18n('PLUGIN_TITLE', true); ?></h3>
	<p>Provides a CSS style-able directory listing. The plugin includes a bunch of filetype icons.</p>
	<p>Now with a configuration page to specify the directory path and URL, as well as any file-types to be ignored in the directory listing.</p>

	<hr>

	<form name="settings" method="post">
		<label><?php simpledir_i18n('LABEL_FULLPATH', true); ?> (e.g. <code>/home/user/data/uploads/</code>): </label>
		<p><input class="text" name="dirpath" type="text" size="90" value="<?php echo $simpledir_conf['dirpath']; ?>"></p>

		<label><?php simpledir_i18n('LABEL_BASEURL', true); ?> (e.g. <code>/data/uploads/</code>):</label>
		<p><input class="text" name="urlpath" type="text" size="90" value="<?php echo $simpledir_conf['urlpath']; ?>"></p>

		<label><?php simpledir_i18n('LABEL_IGNORE', true); ?> (e.g. <code>php,txt</code>):</label>
		<p><input class="text" name="ignore" type="text" size="90" value="<?php echo implode(',', $simpledir_conf['ignore']); ?>"></p>

		<input name='submit_settings' class='submit' type='submit' value='<?php i18n('BTN_SAVESETTINGS'); ?>'><br />
	</form>

	<hr>
	
	<h4 id="usage">Usage:</h4>
	<ul>
		<li>
			<p>Create a page and include <code>(% simpledir %)</code> on that page - this will be replaced with a table. Parameters given below. </p>
			<code class="cke">(% simpledir %)</code>
		</li>
		<li>
			<p>Outside of pages, use function <code>get_simpledir_display</code> - API details given below. </p>
			<code class="tpl">&lt;?php get_simpledir_display(); ?&gt;</code>
		</li>
	</ul>
	<h4 id="parameters">Parameters</h4>
	<p>Additional parameters for the <code>(% simpledir %)</code> shortcode (separated by <code class="cke">|</code>): </p>
	<ul>
		<li>
			<code class="cke">dirpath</code>: path relative to global path given in the admin panel.
		</li>
		<li>
			<code class="cke">urlpath</code>: path relative to the global URL path for the subdirectories.
		</li>
		<li>
			<code class="cke">ignore</code>: comma separated list (no spaces) of extensions to ignore, e.g. <code>php,htaccess</code>. Leave empty to use value given in admin panel.
		</li>
		<li>
			<code class="cke">key</code>: alphanumeric identifier (used in the URL query string) to distinguish this instance of SimpleDir, e.g. <code>subdir2</code>. Needed if you have multiple instances of SimpleDir on the same page.
		</li>
		<li>
			<code class="cke">columns</code>: comma separated list (no spaces) of columns to include, e.g. <code>name,date,size</code>. Leave empty to default to <code>name,date,size</code>.
		</li>
		<li>
			<code class="cke">order</code>: +/- for ascending/descending, followed by column to sort on, e.g. <code>-date</code> for latest file first. Leave empty for <code>+name</code>.
		</li>
		<li>
			<code class="cke">showfilter</code>: set to <code>true</code> to show a search field for filtering files in the current directory
		</li>
		<li>
			<code class="cke">showinitial</code>: number of files to show initially. Set to <code>0</code> to show all
		</li>
		<li>
			<code class="cke">sortable</code>: set to <code>true</code> to allow user to sort files by column
		</li>
		<li>
			<code class="cke">LABEL_NAME</code>: label for the <code>Name</code> column
		</li>
		<li>
			<code class="cke">LABEL_SIZE</code>: label for the <code>Size</code> column
		</li>
		<li>
			<code class="cke">LABEL_DATE</code>: label for the <code>Date</code> column
		</li>
	</ul>
	<h4 id="example">Example</h4>
	<p>
		// Shows all files in /data/uploads/ <br>
		<code>(% simpledir key="subdir1" %)</code>
	</p>
	<p>
		// Shows all files in /data/uploads/images <br>
		<code>(% simpledir key="subdir2" | dirpath="images/" | urlpath="images/" %)</code>
	</p>
	<p>
		// Shows all non-png files in data/uploads/<br>
		<code>(% simpledir key="subdir3" | ignore="png" %)</code>
	</p>
	
	<h4 id="api">API</h4>
	<p><b>Display functions</b></p>
	<ul>
		<li>
			<code>get_simpledir_display($params = array())</code>: prints out a table of the contents of <code>$dirpath</code>. <code>$params</code> is an array with the following keys: <ul>
				<li>
					<code class="tpl">dirpath</code>: same as <code>dirpath</code> above.
				</li>
				<li>
					<code class="tpl">urlpath</code>: same as <code>urlpath</code> above.
				</li>
				<li>
					<code class="tpl">ignore</code>: array of extensions to ignore, e.g. <code>array(&#39;php&#39;, &#39;htaccess&#39;)</code>
				</li>
				<li>
					<code class="tpl">key</code>: same as <code>key</code> above.
				</li>
				<li>
					<code class="tpl">columns</code>: same as above, but an array.
				</li>
				<li>
					<code class="tpl">order</code>: same as above.
				</li>
				<li>
					<code class="tpl">showfilter</code>: same as above.
				</li>
				<li>
					<code class="tpl">showinitial</code>: same as above.
				</li>
				<li>
					<code class="tpl">sortable</code>: same as above.
				</li>
				<li>
					<code class="tpl">LABEL_[NAME]</code> : same as above.
				</li>
			</ul>
		</li>
	</ul>
	
	<p><b>Public functions</b></p>
	<ul>
		<li>
			<code class="tpl">return_simpledir_display($params = array())</code>: returns a string of the table. Same arguments as <code class="tpl">get_simpledir_display</code>
		</li>
		<li>
			<code>return_simpledir_results($params = array())</code> : returns an array of the contents of a given directory <ul>
				<li>
					<code>$params</code> is an array with the following parameters: <ul>
						<li>
							<code class="tpl">dirpath</code>: same as <code>dirpath</code> above.
						</li>
						<li>
							<code class="tpl">urlpath</code>: same as <code>urlpath</code> above.
						</li>
						<li>
							<code class="tpl">ignore</code>: same as <code>ignore</code> above.
						</li>
						<li>
							<code class="tpl">order</code>: same as above.
						</li>
					</ul>
				</li>
				<li>Returned is an array with the following keys: <ul>
						<li>
							<code class="tpl">subdirs</code> : array of subdirectories; each an associative array with keys <code>name</code>, <code>date</code>
						</li>
						<li>
							<code class="tpl">files</code>: array of files, each an associative array with keys <code>name</code>, <code>date</code>, <code>size</code>, <code>type</code>
						</li>
						<li>
							<code class="tpl">total</code>: total size of the files in this directory
						</li>
					</ul>
				</li>
			</ul>
		</li>
	</ul>
	
	<p><b>Credits</b></p>
	<ul>
		<li>
			<a href="https://github.com/lokothodida/gs-simpledir/" target="_blank">Lawrence Okoth-Odida</a> Updated to v.4
		</li>
		<li>
			<a href="http://ffaat.poweredbyclear.com/" target="_blank">Rob Antonishen</a> for the original plugin
		</li>
	</ul>
</div>