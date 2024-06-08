from django import forms
from django.contrib.auth.forms import UserCreationForm, AuthenticationForm
from .models import User

class RegistroForm(UserCreationForm):
    email = forms.EmailField(required=True)
    name = forms.CharField(max_length=255, required=True)

    class Meta:
        model = User
        fields = ['email', 'name', 'password1', 'password2']

class LoginForm(AuthenticationForm):
    username = forms.EmailField(label='Email', max_length=255)
