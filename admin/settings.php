				
	<div class="logichop_settings_container">
		
		<p>	
			Getting started with your ConvertKit integration? <a href="https://logichop.com/docs/using-logic-hop-with-convertkit/" target="_blank">View the instructions</a>
		</p>
		
		<h2>ConvertKit Links</h2>
		<p>
			To make ConvertKit user data available to Logic Hop you will need to enable <strong>Add subscriber_id parameter in email links</strong>. This will automatically append the ConvertKit subscriber_id to your ConvertKit links and allow Logic Hop to access the user's data.
		</p>
		<p>
			To enable subscriber_id in email links:
		</p>
		
		<ol>
			<li>Log in to your ConvertKit account</li>
			<li>Click on the <strong>Email</strong> tab to open <strong>Email Settings</strong></li>
			<li>Under <strong>Advanced</strong> check <strong>Add subscriber_id parameter in email links</strong></li>
			<li>Click <strong>Save Settings</strong></li>
		</ol>
		
		<p>
			Logic Hop uses cookies to access users' ConvertKit data on future visits.
		</p>
		
		<p>
			Additional query string parameters can be added as necessary. Append with an ampersand: <em>&utm_campaign=spring+campaign</em>
		</p>
		
		<hr>
		
		<h3>ConvertKit Forms</h3>
		<p>
			Use the following settings for your ConvertKit forms to enable data for new users.
		</p>
		
		<ul class="logichop-ul">
			<li>Check <em>Send incentive / double opt-in email to confirm new subscribers</em></li>
			<li>Select <em>Thank you page: URL</em></li>
			<li>Check <em>Special Options: Send subscriber data to Thank you page</em></li>
			<li>Append the ConvertKit Form Query String to the Thank you page URL</em>
				<ul class="logichop-ul">
					<li><em>Example:</em> <?php echo site_url(); ?>/thank-you/?convertkit=true</li>
				</ul>
			</li>
		</ul>
		
		<p>
			<label>ConvertKit Form Query String</label>
			<input type="text" onfocus="this.select();" readonly="readonly" value="?convertkit=true">
		</p>
		
		<p>
			ConvertKit user data is available once users confirm their subscription.
		</p>
		
	</div>		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		