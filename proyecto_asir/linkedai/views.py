# mi_aplicacion/views.py
from django.shortcuts import render, redirect
from django.contrib.auth import login, authenticate, logout
from django.contrib.auth.decorators import login_required
from .forms import RegistroForm, LoginForm

def index(request):
    return render(request, 'index.html')

def registro(request):
    if request.method == 'POST':
        form = RegistroForm(request.POST)
        if form.is_valid():
            user = form.save(commit=False)
            user.backend = 'django.contrib.auth.backends.ModelBackend'
            user.save()
            login(request, user, backend='django.contrib.auth.backends.ModelBackend')
            return redirect('index')  # Redirige a la página de inicio
    else:
        form = RegistroForm()
    return render(request, 'registro.html', {'form': form})

def iniciar_sesion(request):
    if request.method == 'POST':
        form = LoginForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data.get('username')
            password = form.cleaned_data.get('password')
            user = authenticate(request, username=username, password=password)
            if user is not None:
                login(request, user, backend='django.contrib.auth.backends.ModelBackend')
                return redirect('index')
    else:
        form = LoginForm()
    return render(request, 'iniciar_sesion.html', {'form': form})

@login_required
def pagina1(request):
    return render(request, 'pagina1.html')

def cerrar_sesion(request):
    logout(request)
    return redirect('index')