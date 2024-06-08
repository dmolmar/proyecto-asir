from django.urls import path
from django.contrib import admin
from . import views

urlpatterns = [
    path('registro/', views.registro, name='registro'),
    path('iniciar_sesion/', views.iniciar_sesion, name='iniciar_sesion'),
    path('cerrar_sesion/', views.cerrar_sesion, name='cerrar_sesion'),
    path('', views.index, name='index'),
]