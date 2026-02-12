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
      id: _str(json['id']) ?? '',
      name: _str(json['name']) ?? '',
      email: _str(json['email']) ?? '',
      phone: _str(json['phone']),
      country: _str(json['country']),
      tenantId: _str(json['tenant_id']),
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

String? _str(dynamic v) => v == null ? null : v.toString();
