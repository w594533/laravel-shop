<?php

return [
    'alipay' => [
        'app_id'         => '2016092300580368',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4yLC4yUlLJ6Pr1U5XqaeaLsRofOwa551tyhjCtNz8QbggNM3eRA2vZ7sliZ/6BrQNxXBuGQnd47V+Zr2rWRl8ZhBfUGI8PKtwz3h8I2FKey3rtWYtlR0fqioH93+vtk0iieupDdTT13vXI9L+dV+Ix45LATl5uQAE0oD0MFh5BtS1rxbY3nDfqlXxfwQqEnEod12Xz2Da35UKtXPGsS1bkM6tTPb2S8CJoOGhhS2KfNA72/KLySGHJvl0u4ecIEws17H5V4GcTYH3HnECokT3jLp4UzbKCjQhs6Dy03QHuIE2Bj1OjnTm2l43HRNquWp/2AbmMdKZOilnlhw2Ze6hwIDAQAB',
        'private_key'    => 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDHeT9tK5OGruUJcseIRe27zPzVT/au4nQ9+u3XUJsMUO7+pdaPpPkQiuUVOwmnMav5d6trsKfW/r6oZM3XKNaJMStpgB89PrlYUTvlwMB2JbQMxVtopXESOL6eNHhAuxLc/hDUD8DgfHu4XGILyFfjOLwVnKy8X2c1HQYMActtC/DCsWKfZJAk3+cO98UBzAq0ccT/FBtTGNf3j45ro5x+lSZp6zrWjPiyJkgqg6TLt+UtN/Bif2OWJV+2Yh2q+cN3TylOFizy883hE8xntjgpNDyAgMI/i0wwml8E1BsfKFnTYceVfzU8Se8Fj/iPzgYYh9Dc7gf3Kr03oiay1AQhAgMBAAECggEAa691IkaIGgmI9BTWyyaVNPFw/OdyUO9Hub4hcD+/tGJs42Q79Mgm/Vs+WLvKdkxGfyKvlC+GLHbSDq58wU72opG80fOs6a9W9+rC30GzpRStosdYlaHa7O9dWKMS2D8l68/s3c6zXX1WfSzCcYHykGQsha5TQk5utSm5/flqDK+ViNH6D92/9ZJePy4V6QiyC0Vj9PfEcXIQVF4ILlMT3n0AbGTP42nTyWeAAfVHeFuj3YS37s1KZrIMdRkkM+fybgpJqsxX8mpx9zsNhrfyeSfPZdWPC0mmuKx5ZuSkFNI5grkgFHKO5mMGupusJUse36dCiGhC/awAnkgMQbHAAQKBgQDikdU9YiPhBzVkBb7JaAgeumCCNKwOkd19Zk1/8l+du0vxbx3pcp0S/Ve5qhniznkXSxsDB/UzJSOmEVmgqgjb4ajhDFIWNOTxputC6UJzXoVVrWCyId1RBhOOoMlyIF4003Imqu7ilnbstpcGNVE23iisrM8T5CNQtbejuCt2oQKBgQDhYmKBq2klwfyunOMLHtNxHUuHT3V/gTEnLRk20UlOBrAMiU7V3ETv9SbwJPkhblxTCorBRu/FJnJRt4Qfe0gXuy7sgAHy64n7CgxBH7YgTRzD0iJfPV336F5z3WnU8u9vYEv+ejA1SvVilvcLpH/2ipYAVILIJ2l+qsdT4F0dgQKBgQCpFoj77Jg2Qnj4EsmV6gvVUubfhYu6u+V+xrCkAjCVuMgugOzj4mOTXnrv1yDGga0hUy3vjyrRZrA6Kcyn+P3vi3PLsLQ6WnNaXWZKY2byTuJXVLNLfbZ713sVAK4WfE2SQxN4BC2P8RcEBiiju3rG5fmZFMbeY25vt447oyDcwQKBgQDDA5/8VtZ6Jyl95J3HR3roitKJV8Vw99YR8cG5XskwzDSUBFLEVP0JK3Pvoe42cQlNiPeaWMiqK6QK9OFLZB5y+jGVzQTirc8L+hu0Vb3+oPpcrXu0MKMYGGVxl4k1CqcGFaprnEoOMre0AK/t0P4v0qYLzxQCH6f2Q8qI1r97AQKBgQDfkdkRStA7Oh6HrDXQSqf6w5eoY5BF+hR/hUv39nswrrAd99YwurG+0cZGeEXc6HYSHJnDYppukj8TZEjWGKqH0e9bWbamsaga9dE6Lzg8cZ1oZkDrTzDGIgM1/08dfW4MA/ebgULVRvuupZJcjUsJdTPeOnqTJjvsQaR3ykHbyg==',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];
