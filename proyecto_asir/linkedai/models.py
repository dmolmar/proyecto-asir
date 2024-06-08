import uuid
from django.contrib.auth.models import AbstractBaseUser, BaseUserManager, PermissionsMixin
from django.contrib.auth import get_user_model
from django.db import models

class UserManager(BaseUserManager):
    def create_user(self, email, password=None, **extra_fields):
        if not email:
            raise ValueError('El email es obligatorio')
        email = self.normalize_email(email)
        user = self.model(email=email, **extra_fields)
        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, email, password=None, **extra_fields):
        extra_fields.setdefault('is_staff', True)
        extra_fields.setdefault('is_superuser', True)

        if extra_fields.get('is_staff') is not True:
            raise ValueError('Superuser must have is_staff=True.')
        if extra_fields.get('is_superuser') is not True:
            raise ValueError('Superuser must have is_superuser=True.')

        return self.create_user(email, password, **extra_fields)

class User(AbstractBaseUser, PermissionsMixin):
    email = models.EmailField(unique=True)
    name = models.CharField(max_length=255)
    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)
    is_superuser = models.BooleanField(default=False)

    objects = UserManager()

    USERNAME_FIELD = 'email'
    REQUIRED_FIELDS = ['name']

    def __str__(self):
        return self.email
    
def generate_unique_id():
    id = uuid.uuid4().hex[:8]  # Genera un ID de 8 caracteres
    while Clase.objects.filter(id=id).exists():  # Comprueba si el ID ya existe
        id = uuid.uuid4().hex[:8]  # Si el ID ya existe, genera uno nuevo
    return id

def upload_to(instance, filename):
    return 'clases/%s/%s' % (instance.id, filename)

class Clase(models.Model):
    id = models.CharField(primary_key=True, default=generate_unique_id, editable=False, max_length=8)
    nombre = models.CharField(max_length=255)
    creador = models.ForeignKey(get_user_model(), on_delete=models.CASCADE, related_name='clases_creadas')
    moderadores = models.ManyToManyField(get_user_model(), related_name='clases_moderadas')
    usuarios = models.ManyToManyField(get_user_model(), related_name='clases_unidas', blank=True)
    imagen = models.ImageField(upload_to=upload_to, blank=True, null=True)

    def __str__(self):
        return self.nombre

class Post(models.Model):
    autor = models.ForeignKey(get_user_model(), on_delete=models.CASCADE)
    clase = models.ForeignKey(Clase, on_delete=models.CASCADE)
    texto = models.TextField()
    archivos = models.FileField(upload_to='posts/', blank=True, null=True)