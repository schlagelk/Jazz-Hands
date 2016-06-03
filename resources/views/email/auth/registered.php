{% extends 'email/templates/default.php' %}

{% block content %}
	<p>You have registered!</p>
	<p>Activate your account using this link: {{ url }}{{ path_for('activate') }}?email={{ user.email }}&identifier={{ user.active_hash }}</p>
{%endblock%}