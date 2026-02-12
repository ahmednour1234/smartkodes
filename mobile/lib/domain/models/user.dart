class User {
  final String id;
  final String name;
  final String email;
  final String? phone;
  final String? country;
  final String? tenantId;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.country,
    this.tenantId,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      country: json['country'] as String?,
      tenantId: json['tenant_id'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone': phone,
        'country': country,
        'tenant_id': tenantId,
      };
}
