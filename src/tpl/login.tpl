<div class="container">
	<form method="post" action="/">
		<input type="text" size="40" placeholder="email" name="loginName">
		<input type="password" size="20" placeholder="Password" name="passwd">        
		<button type="submit">Sign in</button>
	</form>
	<br />
	<form method="post" action="/tracking/register">
		<input type="text" size="40" placeholder="email" name="email">
		<button type="submit">Create Account</button>
	</form>
	<br />
	<form method="post" action="/tracking/forgotPass">
		<input type="text" size="40" placeholder="email" name="email">
		<button type="submit">Forgot Password</button>
	</form>

	<br />
	Your email will only be used for registration verification and 'Forgot Password'.
	<br />
	<br />
	tracking.theora.com/pixel?uid=myUid&name=...[&value=...][&oid=...][pid=...][&cid=...]
	<br />
	API to add descriptions:<br />
	tracking.theora.com/api/describe?what=...&key=...&value=...
	<br />
	tracking.theora.com/describe - passworded UI for descriptions
	<br />
	<br />
	e.g.
	<br />
	/pixel?uid=123456&name=pageView&pid=156
	<br />
	/api/describe?uid=123456&what=pid&key=156&description=thePageDescriptionToAppearOnReports
	<br />
	<br />
	This demo is in 'brief' mode: Only an aggregate dashboard and drill down are reported.
	<br />
	The proof details are not stored.
	<br />
</div>
