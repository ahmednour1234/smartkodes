class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final ApiMeta? meta;

  const ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.meta,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic)? fromJsonT,
  ) {
    return ApiResponse(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : json['data'] as T?,
      meta: json['meta'] != null
          ? ApiMeta.fromJson(json['meta'] as Map<String, dynamic>)
          : null,
    );
  }
}

class ApiMeta {
  final String? timestamp;
  final PaginationMeta? pagination;

  ApiMeta({this.timestamp, this.pagination});

  factory ApiMeta.fromJson(Map<String, dynamic> json) {
    return ApiMeta(
      timestamp: json['timestamp'] as String?,
      pagination: json['pagination'] != null
          ? PaginationMeta.fromJson(json['pagination'] as Map<String, dynamic>)
          : null,
    );
  }
}

class PaginationMeta {
  final int currentPage;
  final int perPage;
  final int total;
  final int lastPage;
  final int? from;
  final int? to;

  PaginationMeta({
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.lastPage,
    this.from,
    this.to,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    return PaginationMeta(
      currentPage: json['current_page'] as int? ?? 1,
      perPage: json['per_page'] as int? ?? 15,
      total: json['total'] as int? ?? 0,
      lastPage: json['last_page'] as int? ?? 1,
      from: json['from'] as int?,
      to: json['to'] as int?,
    );
  }
}

class PaginatedResponse<T> {
  final List<T> data;
  final PaginationMeta pagination;
  final ApiLinks? links;

  PaginatedResponse({
    required this.data,
    required this.pagination,
    this.links,
  });

  factory PaginatedResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic) fromJsonT,
  ) {
    final meta = json['meta'] as Map<String, dynamic>?;
    final pagination = meta?['pagination'] != null
        ? PaginationMeta.fromJson(meta!['pagination'] as Map<String, dynamic>)
        : PaginationMeta(currentPage: 1, perPage: 15, total: 0, lastPage: 1);
    final list = json['data'] as List<dynamic>? ?? [];
    return PaginatedResponse(
      data: list.map((e) => fromJsonT(e)).toList(),
      pagination: pagination,
      links: json['links'] != null
          ? ApiLinks.fromJson(json['links'] as Map<String, dynamic>)
          : null,
    );
  }
}

class ApiLinks {
  final String? first;
  final String? last;
  final String? prev;
  final String? next;

  ApiLinks({this.first, this.last, this.prev, this.next});

  factory ApiLinks.fromJson(Map<String, dynamic> json) {
    return ApiLinks(
      first: json['first'] as String?,
      last: json['last'] as String?,
      prev: json['prev'] as String?,
      next: json['next'] as String?,
    );
  }
}
