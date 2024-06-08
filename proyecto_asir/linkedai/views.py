from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import login, authenticate, logout
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from .forms import RegistroForm, LoginForm, ClaseForm
from .models import Clase

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

def index(request):
    form = ClaseForm()
    clases_creadas = request.user.clases_creadas.all()
    clases_unidas = request.user.clases_unidas.all()
    if request.method == 'POST':
        if 'añadirClaseBtn' in request.POST:
            form = ClaseForm(request.POST, request.FILES)
            if form.is_valid():
                nueva_clase = form.save(commit=False)
                nueva_clase.creador = request.user
                nueva_clase.save()
                messages.success(request, 'Clase añadida exitosamente.')
                return redirect('index')
            else:
                messages.error(request, 'Error al añadir la clase.')
        elif 'unirseClaseBtn' in request.POST:
            id = request.POST.get('id')
            try:
                clase = Clase.objects.get(id=id)
                clase.usuarios.add(request.user)
                messages.success(request, 'Te has unido a la clase exitosamente.')
                return redirect('clase', id=id)
            except Clase.DoesNotExist:
                messages.error(request, 'No existe una clase con esa ID.')
    return render(request, 'index.html', {
        'form': form,
        'clases_creadas': clases_creadas,
        'clases_unidas': clases_unidas
    })

def clase(request, id):
    clase = get_object_or_404(Clase, id=id)
    return render(request, 'clase.html', {'clase': clase})

@login_required
def cerrar_sesion(request):
    logout(request)
    return redirect('index')