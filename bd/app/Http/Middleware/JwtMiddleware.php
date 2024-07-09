<?php
namespace App\Http\Middleware;

use Closure;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
  

use Illuminate\Support\Facades\Log;

class JwtMiddleware 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();

            $payload = JWTAuth::parseToken()->getPayload();
            Log::info('JWT Payload', $payload->toArray());

            // $request->attributes->add(['jwt_payload' => $payload->toArray()]);
             // Extract email and role from the payload
             $email = $payload->get('email');
             $role = $payload->get('role');
 
             // Adding email and role to request attributes
             $request->attributes->add(['email' => $email, 'role' => $role]);
        }  catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Unauthorized: token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Unauthorized: token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
            
        
        return $next($request);
    }
}
