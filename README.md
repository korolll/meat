# TEALSY

## Полезные команды

0. Перекомпиляция документации
    ```bash
    npm run doc-build-all
    ```



### Фильтрация параметров номенклатур

```POST /api/profile/price-lists/{price_list_uuid}/products```

0. Multiple condition 
    ```
    (((condition1) OR (condition2)) AND ((condition3) OR (condition4)))
    ```
    ```json
    {
      "assortment_properties": {
        "AND": [
          {
            "OR": [
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": "=",
                "value": "Intel Core i9 9900k"
              },
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": "=",
                "value": "Intel Core i7 8900k"
              }
            ]
          },
          {
            "OR": [
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": "=",
                "value": "GeForce RTX 2080 TI"
              },
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": "=",
                "value": "GeForce RTX 2080"
              }
            ]
          }
        ]
      }
    }
    ```

0. Single condition 
    ```
    (condition1)
    ```
    ```json
    {
      "assortment_properties": {
        "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
        "operator": "=",
        "value": "qwqweqweqw"
      }
    }
    ```

0. Multiple and Single condition 
    ```
    (((condition1) OR (condition2)) AND (condition3))
    ```
    ```json
    {
      "assortment_properties": {
        "AND": [
          {
            "OR": [
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": "=",
                "value": "qwqweqweqw"
              },
              {
                "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
                "operator": ">",
                "value": 1
              }
            ]
          },
          {
            "uuid": "8d4c2f7d-1885-4fc9-8d65-9a163a0e85fd",
            "operator": "=",
            "value": "qwqweqweqw"
          }
        ]
      }
    }
    ```
