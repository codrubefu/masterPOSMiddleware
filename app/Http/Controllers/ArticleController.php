<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Search for articles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Get search parameters
        $query = $request->get('query', '');
        $category = $request->get('category', '');
        $limit = $request->get('limit', 10);

        // Demo articles data
        $demoArticles = [
            [
                'id' => 1,
                'title' => 'Getting Started with Laravel',
                'content' => 'Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling.',
                'category' => 'Technology',
                'author' => 'John Doe',
                'published_at' => '2024-01-15',
                'tags' => ['Laravel', 'PHP', 'Web Development'],
                'image_url' => 'https://example.com/images/laravel-guide.jpg'
            ],
            [
                'id' => 2,
                'title' => 'Advanced PHP Techniques',
                'content' => 'Explore advanced PHP programming techniques including design patterns, performance optimization, and modern PHP features.',
                'category' => 'Technology',
                'author' => 'Jane Smith',
                'published_at' => '2024-02-20',
                'tags' => ['PHP', 'Programming', 'Best Practices'],
                'image_url' => 'https://example.com/images/php-advanced.jpg'
            ],
            [
                'id' => 3,
                'title' => 'Database Design Best Practices',
                'content' => 'Learn how to design efficient and scalable databases with proper indexing, normalization, and optimization techniques.',
                'category' => 'Database',
                'author' => 'Mike Johnson',
                'published_at' => '2024-03-10',
                'tags' => ['Database', 'MySQL', 'Optimization'],
                'image_url' => 'https://example.com/images/database-design.jpg'
            ],
            [
                'id' => 4,
                'title' => 'RESTful API Development',
                'content' => 'Master the art of building RESTful APIs with proper HTTP methods, status codes, and API documentation.',
                'category' => 'API',
                'author' => 'Sarah Wilson',
                'published_at' => '2024-04-05',
                'tags' => ['API', 'REST', 'Web Services'],
                'image_url' => 'https://example.com/images/api-development.jpg'
            ],
            [
                'id' => 5,
                'title' => 'Frontend Frameworks Comparison',
                'content' => 'A comprehensive comparison of popular frontend frameworks including React, Vue.js, and Angular.',
                'category' => 'Frontend',
                'author' => 'David Brown',
                'published_at' => '2024-05-12',
                'tags' => ['Frontend', 'React', 'Vue.js', 'Angular'],
                'image_url' => 'https://example.com/images/frontend-frameworks.jpg'
            ]
        ];

        // Filter articles based on search parameters
        $filteredArticles = collect($demoArticles);

        // Filter by query (search in title and content)
        if (!empty($query)) {
            $filteredArticles = $filteredArticles->filter(function ($article) use ($query) {
                return stripos($article['title'], $query) !== false ||
                       stripos($article['content'], $query) !== false ||
                       in_array($query, array_map('strtolower', $article['tags']));
            });
        }

        // Filter by category
        if (!empty($category)) {
            $filteredArticles = $filteredArticles->filter(function ($article) use ($category) {
                return stripos($article['category'], $category) !== false;
            });
        }

        // Limit results
        $filteredArticles = $filteredArticles->take($limit);

        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => [
                'articles' => $filteredArticles->values(),
                'total' => $filteredArticles->count(),
                'search_params' => [
                    'query' => $query,
                    'category' => $category,
                    'limit' => $limit
                ]
            ]
        ]);
    }

    /**
     * Get a single article by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Demo articles data (same as above for consistency)
        $demoArticles = [
            1 => [
                'id' => 1,
                'title' => 'Getting Started with Laravel',
                'content' => 'Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects.',
                'category' => 'Technology',
                'author' => 'John Doe',
                'published_at' => '2024-01-15',
                'tags' => ['Laravel', 'PHP', 'Web Development'],
                'image_url' => 'https://example.com/images/laravel-guide.jpg',
                'read_time' => '5 minutes',
                'views' => 1250
            ],
            2 => [
                'id' => 2,
                'title' => 'Advanced PHP Techniques',
                'content' => 'Explore advanced PHP programming techniques including design patterns, performance optimization, and modern PHP features. This comprehensive guide covers everything from SOLID principles to advanced OOP concepts.',
                'category' => 'Technology',
                'author' => 'Jane Smith',
                'published_at' => '2024-02-20',
                'tags' => ['PHP', 'Programming', 'Best Practices'],
                'image_url' => 'https://example.com/images/php-advanced.jpg',
                'read_time' => '8 minutes',
                'views' => 890
            ],
            3 => [
                'id' => 3,
                'title' => 'Database Design Best Practices',
                'content' => 'Learn how to design efficient and scalable databases with proper indexing, normalization, and optimization techniques. Understand the principles of good database architecture.',
                'category' => 'Database',
                'author' => 'Mike Johnson',
                'published_at' => '2024-03-10',
                'tags' => ['Database', 'MySQL', 'Optimization'],
                'image_url' => 'https://example.com/images/database-design.jpg',
                'read_time' => '12 minutes',
                'views' => 2100
            ],
            4 => [
                'id' => 4,
                'title' => 'RESTful API Development',
                'content' => 'Master the art of building RESTful APIs with proper HTTP methods, status codes, and API documentation. Learn best practices for API versioning and authentication.',
                'category' => 'API',
                'author' => 'Sarah Wilson',
                'published_at' => '2024-04-05',
                'tags' => ['API', 'REST', 'Web Services'],
                'image_url' => 'https://example.com/images/api-development.jpg',
                'read_time' => '10 minutes',
                'views' => 1680
            ],
            5 => [
                'id' => 5,
                'title' => 'Frontend Frameworks Comparison',
                'content' => 'A comprehensive comparison of popular frontend frameworks including React, Vue.js, and Angular. Understand the pros and cons of each framework.',
                'category' => 'Frontend',
                'author' => 'David Brown',
                'published_at' => '2024-05-12',
                'tags' => ['Frontend', 'React', 'Vue.js', 'Angular'],
                'image_url' => 'https://example.com/images/frontend-frameworks.jpg',
                'read_time' => '15 minutes',
                'views' => 3200
            ]
        ];

        // Find article by ID
        if (!isset($demoArticles[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Article retrieved successfully',
            'data' => $demoArticles[$id]
        ]);
    }

    /**
     * Get all articles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Demo articles data
        $demoArticles = [
            [
                'id' => 1,
                'title' => 'Getting Started with Laravel',
                'content' => 'Laravel is a web application framework with expressive, elegant syntax...',
                'category' => 'Technology',
                'author' => 'John Doe',
                'published_at' => '2024-01-15',
                'tags' => ['Laravel', 'PHP', 'Web Development'],
                'image_url' => 'https://example.com/images/laravel-guide.jpg'
            ],
            [
                'id' => 2,
                'title' => 'Advanced PHP Techniques',
                'content' => 'Explore advanced PHP programming techniques...',
                'category' => 'Technology',
                'author' => 'Jane Smith',
                'published_at' => '2024-02-20',
                'tags' => ['PHP', 'Programming', 'Best Practices'],
                'image_url' => 'https://example.com/images/php-advanced.jpg'
            ],
            [
                'id' => 3,
                'title' => 'Database Design Best Practices',
                'content' => 'Learn how to design efficient and scalable databases...',
                'category' => 'Database',
                'author' => 'Mike Johnson',
                'published_at' => '2024-03-10',
                'tags' => ['Database', 'MySQL', 'Optimization'],
                'image_url' => 'https://example.com/images/database-design.jpg'
            ],
            [
                'id' => 4,
                'title' => 'RESTful API Development',
                'content' => 'Master the art of building RESTful APIs...',
                'category' => 'API',
                'author' => 'Sarah Wilson',
                'published_at' => '2024-04-05',
                'tags' => ['API', 'REST', 'Web Services'],
                'image_url' => 'https://example.com/images/api-development.jpg'
            ],
            [
                'id' => 5,
                'title' => 'Frontend Frameworks Comparison',
                'content' => 'A comprehensive comparison of popular frontend frameworks...',
                'category' => 'Frontend',
                'author' => 'David Brown',
                'published_at' => '2024-05-12',
                'tags' => ['Frontend', 'React', 'Vue.js', 'Angular'],
                'image_url' => 'https://example.com/images/frontend-frameworks.jpg'
            ]
        ];

        $total = count($demoArticles);
        $offset = ($page - 1) * $perPage;
        $articles = array_slice($demoArticles, $offset, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => [
                'articles' => $articles,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]
        ]);
    }
}
