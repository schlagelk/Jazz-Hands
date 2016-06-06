{% extends 'email/templates/default.php' %}

{% block content %}
	<p>Please reset your password by clicking on this link: {{ url }}{{ path_for('auth.password.reset') }}?email={{ user.email }}&identifier={{ identifier|url_encode }}</p>
{%endblock%}