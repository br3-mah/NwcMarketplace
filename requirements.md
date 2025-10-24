Also in the sign otp apis also infuse the mechanism where returning user 
trying to signin with otps using the same apis (when already exists with the specified role) 
only get a token and a welcome back message


modify the front.blade.php layout and make a new landing page with simple and little sections 
just a herro or brand for a newworld cargo marketplace cloud server running status  


implement the checkout APIS into [api.php](routes/api.php) following the requirements @roadmap.yml align and infuse according the current system logic flow implementation of messages [web.php](routes/web.php) http://localhost:8000/user/che

/checkout/estimate:
    post:
      summary: Estimate totals and shipping
      requestBody:
        required: true
        content:
          application/json:
            schema: { $ref: '#/components/schemas/GenericPayload' }
      responses: { '200': { description: OK } }
  /checkout/create-order:
    post:
      summary: Create order
      requestBody:
        required: true
        content:
          application/json:
            schema: { $ref: '#/components/schemas/GenericPayload' }
      responses: { '201': { description: Created } }