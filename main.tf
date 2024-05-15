# Configure the AWS provider
provider "aws" {
  region = "us-east-1"
}

# Configure the Kubernetes provider
provider "kubernetes" {
  host                   = aws_eks_cluster.proyecto14_eks_cluster.endpoint
  token                  = data.aws_eks_cluster_auth.proyecto14_eks_cluster_auth.token
  cluster_ca_certificate = base64decode(aws_eks_cluster.proyecto14_eks_cluster.certificate_authority[0].data)
}

# Data source to get EKS cluster authentication token
data "aws_eks_cluster_auth" "proyecto14_eks_cluster_auth" {
  name = aws_eks_cluster.proyecto14_eks_cluster.name
}

# Create a VPC
resource "aws_vpc" "proyecto14_vpc" {
  cidr_block = "10.0.0.0/16"
  tags = {
    Name = "proyecto14-vpc"
  }
}

# Create subnets
resource "aws_subnet" "proyecto14_subnet1" {
  cidr_block         = "10.0.1.0/24"
  vpc_id             = aws_vpc.proyecto14_vpc.id
  availability_zone  = "us-east-1a"
  map_public_ip_on_launch = true
  tags = {
    Name = "proyecto14-subnet-1"
  }
}

resource "aws_subnet" "proyecto14_subnet2" {
  cidr_block         = "10.0.2.0/24"
  vpc_id             = aws_vpc.proyecto14_vpc.id
  availability_zone  = "us-east-1b"
  map_public_ip_on_launch = true
  tags = {
    Name = "proyecto14-subnet-2"
  }
}

# Create an Internet Gateway
resource "aws_internet_gateway" "proyecto14_igw" {
  vpc_id = aws_vpc.proyecto14_vpc.id
  tags = {
    Name = "proyecto14-igw"
  }
}

# Create a route table and associate it with subnets
resource "aws_route_table" "proyecto14_public_route_table" {
  vpc_id = aws_vpc.proyecto14_vpc.id
  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.proyecto14_igw.id
  }
  tags = {
    Name = "proyecto14-public-route-table"
  }
}

resource "aws_route_table_association" "proyecto14_subnet1_association" {
  subnet_id      = aws_subnet.proyecto14_subnet1.id
  route_table_id = aws_route_table.proyecto14_public_route_table.id
}

resource "aws_route_table_association" "proyecto14_subnet2_association" {
  subnet_id      = aws_subnet.proyecto14_subnet2.id
  route_table_id = aws_route_table.proyecto14_public_route_table.id
}

# Simplified EKS configuration using the existing LabRole
resource "aws_eks_cluster" "proyecto14_eks_cluster" {
  name     = "proyecto14-eks-cluster"
  role_arn = "arn:aws:iam::373309684881:role/LabRole"

  vpc_config {
    subnet_ids = [
      aws_subnet.proyecto14_subnet1.id,
      aws_subnet.proyecto14_subnet2.id
    ]
    endpoint_public_access = true
    endpoint_private_access = false
  }
}

# Simplified EKS Node Group configuration using the existing LabRole
resource "aws_eks_node_group" "proyecto14_node_group" {
  cluster_name    = aws_eks_cluster.proyecto14_eks_cluster.name
  node_group_name = "proyecto14-node-group"
  node_role_arn   = "arn:aws:iam::373309684881:role/LabRole"

  scaling_config {
    desired_size = 1
    max_size     = 2
    min_size     = 1
  }

  subnet_ids = [
    aws_subnet.proyecto14_subnet1.id,
    aws_subnet.proyecto14_subnet2.id
  ]
}

# Create a Kubernetes deployment with Nginx
resource "kubernetes_deployment" "proyecto14_deployment" {
  metadata {
    name      = "proyecto14-deployment"
    namespace = "default"
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "proyecto14"
      }
    }

    template {
      metadata {
        labels = {
          app = "proyecto14"
        }
      }

      spec {
        container {
          image = "nginx:latest"
          name  = "proyecto14-container"

          port {
            container_port = 80
          }
        }
      }
    }
  }

  depends_on = [
    aws_eks_node_group.proyecto14_node_group
  ]
}

# Create a Kubernetes service
resource "kubernetes_service" "proyecto14_service" {
  metadata {
    name      = "proyecto14-service"
    namespace = "default"
    annotations = {
      "service.beta.kubernetes.io/aws-load-balancer-type" = "nlb"  # use this if you want to use a Network Load Balancer
    }
  }

  spec {
    selector = {
      app = "proyecto14"
    }

    port {
      name        = "http"
      port        = 80
      target_port = 80
    }

    type = "LoadBalancer"
  }

  depends_on = [kubernetes_deployment.proyecto14_deployment]
}
